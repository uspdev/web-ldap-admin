<?php

namespace App\Ldap;

use Adldap\Laravel\Facades\Adldap;
use Adldap\Models\Attributes\AccountControl;
use App\Ldap\Group as LdapGroup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Uspdev\Replicado\Graduacao;
use Uspdev\Replicado\Estrutura;
use Uspdev\Replicado\Pessoa;
use Uspdev\Utils\Generic as Utils;
use \Adldap\Models\User as LdapUser;

class User
{

    /**
     * Cria ou atualiza os dados do usuário ldap
     *
     * Este método está com mais comentários no código pois em geral
     * serve de entrada para novos desenvolvedores.
     *
     * Estrutura do array attr:
     * $attr['nome']  : Nome completo
     * $attr['email'] : Email
     * $attr['setor'] : Departamento
     * $attr['descricao'] : Descricao
     **/
    public static function createOrUpdate(string $username, array $attr, array $groups = [], $password = null)
    {
        // vamos ver se o usuário já existe
        $user = SELF::obterUserPorUsername($username);

        # Novo usuário
        if (is_null($user) or $user == false) {
            $user = Adldap::make()->user();

            // define DN para esse user
            $user->setDn('cn=' . $username . ',cn=Users,' . $user->getDnBuilder());

            // se não for fornecido senha vamos gerar aleatório forte
            $user->setPassword($password ?? Utils::senhaAleatoria());

            // Trocar a senha no próximo logon
            if (config('web-ldap-admin.obrigaTrocarSenhaNoWindows')) {
                $user->setAttribute('pwdlastset', 0);
            }

            // Enable the new user (using user account control).
            $user->setUserAccountControl(AccountControl::NORMAL_ACCOUNT);

            // vamos expirar senha conforme configsobrenome
            $user->setAccountExpiry(SELF::getExpiryDays());
        }

        // login no windows
        $user->setAccountName($username);

        // nome de exibição
        $user->setDisplayName($attr['nome']);

        // atribuindo nome e sobrenome
        $nome_array = explode(' ', $attr['nome']);
        if (count($nome_array) > 1) {
            $user->setFirstName(trim($nome_array[0]));
            unset($nome_array[0]);
            $user->setLastName(implode(' ', $nome_array));
        } else {
            $user->setFirstName(trim($nome_array[0]));
        }

        if (!empty($attr['email'])) {
            $user->setEmail($attr['email']);
        }

        // caso o codpes venha no employeenumber
        if (!empty($attr['employeeNumber'])) {
            $user->setEmployeeNumber($attr['employeeNumber']);
        }

        // caso o codpes venha no physicaldeliveryofficename
        if (!empty($attr['physicalDeliveryOfficeName'])) {
            $user->setPhysicalDeliveryOfficeName($attr['physicalDeliveryOfficeName']);
        }

        // Departamento
        if (!empty($attr['setor'])) {
            $user->setDepartment($attr['setor']);
        }

        // Descrição, informa se a conta foi criada a partir da sincronização
        if (!empty($attr['descricao'])) {
            $user->setDescription($attr['descricao']);
        }

        $user->save();

        // Adiciona a um ou mais grupo
        LdapGroup::addMember($user, $groups);

        // Busca a OU padrão informada no .env
        // Se vazio, não é necessário alterar nada, pois o default é a raiz (Thiago)
        if(config('web-ldap-admin.ouDefault') != ''){
            $ou = Adldap::search()->ous()->find(config('web-ldap-admin.ouDefault'));

            // Move o usuário para a OU padrão somente se ela existir,
            // do contrário deixa o usuário na raiz
            $user->move($ou);
        }



        return $user;
    }

    /**
     * Retorna o número de dias para expirar a conta com base no config
     */
    public static function getExpiryDays()
    {
        if (config('web-ldap-admin.expirarEm') == 0) {
            return null;
        } else {
            return now()->addDays(config('web-ldap-admin.expirarEm'))->timestamp;
        }
    }

    /**
     * Define prazo de expiração para senha da conta
     */
    public static function expirarSenha($username, $expiry)
    {
        $user = SELF::obterUserPorUsername($username);
        if ($user) {
            if ($expiry) {
                $user->setAccountExpiry(now()->addDays($expiry)->timestamp);
            } else {
                $user->setAccountExpiry(null);
            }
            $user->save();
            return true;
        }
        return false;
    }

    /**
     * Obtém uma instância de usuário com busca pelo codpes
     *
     * @param Int $codpes
     * @return \Adldap\Models\User
     */
    public static function obterUserPorCodpes($codpes)
    {
        $user = Adldap::search()->users()->findBy(config('web-ldap-admin.campoCodpes'), $codpes);

        // não vai encontrar se for pelo username, nesse caso vamos usar o CN
        if (is_null($user)) {
            // se estiver usando o prefixo
            $user = Adldap::search()->users()->where('cn', '=', config('web-ldap-admin.prefixUsername') . $codpes)->first();
        }

        return $user;
    }

    /**
     * Obtém uma instância de usuário com busca pelo username
     *
     * @param String $username
     * @return \Adldap\Models\User
     */
    public static function obterUserPorUsername($username)
    {
        return Adldap::search()->users()->where('cn', '=', $username)->first();
    }

    /**
     * Coleta atributos do usuário para serem mostrados
     *
     * @param \Adldap\Models\User $user
     * @return Array
     */
    public static function show(LdapUser $user)
    {
        $attr = [];

        // Nome e email
        $attr['username'] = $user->getAccountName();
        $attr['display_name'] = $user->getDisplayName();
        $attr['email'] = $user->getEmail();
        $attr['description'] = $user->getDescription();
        $attr['codpes'] = SELF::obterCodpes($user);

        // Data da criação da conta
        $ativacao = $user->whencreated[0];
        if (!is_null($ativacao)) {
            // vamos subtrair 3 horas para bater com o TZ local. TODO: alguma forma de melhorar isso?
            $ativacao = Carbon::createFromFormat('YmdHis\.0\Z', $ativacao)->subHours(3)->format('d/m/Y H:i:s');
        }
        $attr['ativacao'] = $ativacao;

        // data da última senha alterada
        $last = $user->getPasswordLastSetDate();
        if (!is_null($last)) {
            $last = Carbon::createFromFormat('Y-m-d H:i:s', $last)->format('d/m/Y H:i:s');
        }
        $attr['senha_alterada_em'] = $last;

        // Data da expiração da conta
        $expira = $user->expirationDate();
        if (!is_null($expira)) {
            $expira = Carbon::instance($expira)->format('d/m/Y');
        }
        $attr['expira'] = $expira;

        // Grupos: vamos ocultar o grupo "Domain Users"
        $grupos = array_diff($user->getGroupNames(), ['Domain Users']);
        sort($grupos);
        $attr['grupos'] = implode(', ', $grupos);

        // Department
        $attr['department'] = $user->getDepartment();

        return $attr;
    }

    /**
     * Retorna o codpes do usuário ldap
     *
     * O codpes pode ser o username ou o employeeNumber e é setado no config.
     * Se não encontrar no campo apropriado faz a busca no outro campo
     * para o caso de ter mudado a regra ao longo do uso
     *
     * Se não retornar codpes o status pode ser qualquer
     *
     * @param \Adldap\Models\User $user
     * @param $status Se true retorna se o codpes veio do campo correto ou não, segundo o config
     * @return Int|Array|Null
     */
    public static function obterCodpes(LdapUser $user, Bool $status = false)
    {
        $valido = true;
        switch (strtolower(config('web-ldap-admin.campoCodpes'))) {
            case 'employeenumber':
                if (!is_numeric($codpes = $user->getEmployeeNumber())) {
                    $codpes = $user->getAccountName();
                    $valido = false;
                }
                break;
            case 'username':
                if (config('web-ldap-admin.prefixUsername') != '') {
                    if (!is_numeric($codpes = $user->getEmployeeNumber())) {
                        $codpes = $user->getAccountName();
                        $valido = false;
                    }
                } else {
                    if (!is_numeric($codpes = $user->getAccountName())) {
                        $codpes = $user->getEmployeeNumber();
                        $valido = false;
                    }
                }
                break;
            default:
                if (!is_numeric($codpes = $user->getAccountName())) {
                    $codpes = $user->getEmployeeNumber();
                    $valido = false;
                }
                break;
        }
        if (is_numeric($codpes)) {
            return $status ? [$codpes, $valido] : $codpes;
        } else {
            return null;
        }
    }

    public static function delete(String $username)
    {
        $user = SELF::obterUserPorUsername($username);
        if (!is_null($user)) {
            $user->delete();
            return true;
        }
        return false;
    }

    public static function disable(String $username)
    {
        $user = SELF::obterUserPorUsername($username);
        if (!is_null($user)) {
            # https://support.microsoft.com/pt-br/help/305144/how-to-use-the-useraccountcontrol-flags-to-manipulate-user-account-pro
            $user->setUserAccountControl(AccountControl::ACCOUNTDISABLE);
            $user->save();
            return true;
        }
        return false;
    }

    public static function enable(String $username)
    {
        $user = SELF::obterUserPorUsername($username);

        if (!is_null($user)) {
            # https://support.microsoft.com/pt-br/help/305144/how-to-use-the-useraccountcontrol-flags-to-manipulate-user-account-pro
            $user->setUserAccountControl(AccountControl::NORMAL_ACCOUNT);
            $user->save();
            return true;
        }
        return false;
    }

    public static function changePassword($username, String $password, $must_change_pwd = null): bool
    {
        $user = SELF::obterUserPorUsername($username);
        if (is_null($user)) {
            return false;
        }

        $user->setPassword($password);
        // Leonardo Ruiz: Alteracao de senha nao deve alterar validade da conta
        //$user->setAccountExpiry(SELF::getExpiryDays());

        if ($must_change_pwd) {
            $user->setEnableForcePasswordChange();
        }

        try {
            $user->save();
            $result = true;
        } catch (\ErrorException $e) {
            echo (Gate::check('gerente')) ? $e->getMessage() : null;
            $result = false;
        }
        return $result;
    }

    public static function getUsersGroup($grupo)
    {
        $ldapusers = [];
        $group = Adldap::search()->groups()->find($grupo);
        if ($group != false) {
            $ldapusers = Adldap::search()->users();
            $ldapusers = $ldapusers->where('memberof', '=', $group->getDnBuilder()->get());
            foreach (config('web-ldap-admin.ocultarUsuarios') as $ocultar) {
                $ldapusers = $ldapusers->where('samaccountname', '!=', $ocultar);
            }
            $ldapusers = $ldapusers->sortBy('displayname', 'asc');
            $ldapusers = $ldapusers->paginate(config('web-ldap-admin.registrosPorPagina'))->getResults();
        }

        return $ldapusers;
    }

    public static function desativarUsers($desligados)
    {
        foreach ($desligados as $desligado) {
            // remover dos grupos
            $user = SELF::obterUserPorUsername($desligado);
            $groups = $user->getGroups();
            foreach ($groups as $group) {
                $group->removeMember($user);
            }

            // adicionar ao grupo Desativados
            LdapGroup::addMember($user, ['Desativados']);

            // desativar conta
            self::disable($desligado);
        }
    }

    /**
     * Cria ou atualiza recebendo o array da pessoa
     *
     * em $pessoa: codema, codpes, dtanas (tabela pessoa), nomabvset, nompesttd (dados da tabela localizapessoa)
     *
     * @param array $pessoa
     * @author Alessandro Costa de Oliveira 11/03/2022
     */
    public static function criarOuAtulizarPorArray($pessoa, $metodo = '')
    {
        // setando username e codpes (similar loginListener)
        switch (strtolower(config('web-ldap-admin.campoCodpes'))) {
            case 'employeenumber':
                $username = explode('@', $pessoa['codema'])[0];
                $username = preg_replace("/[^a-zA-Z0-9]+/", "", $username); //email sem caracteres especiais
                // Se username inicia com número o prefixo é adicionado
                if (is_numeric(substr($username, 0, 1))) {
                    $username = config('web-ldap-admin.prefixUsername') . substr($username, 0, (15 - strlen(config('web-ldap-admin.prefixUsername')))); //limitando em 15 caracteres
                } else {
                    $username = substr($username, 0, 15); //limitando em 15 caracteres
                }
                $attr['employeeNumber'] = $pessoa['codpes'];
                $attr['physicalDeliveryOfficeName'] = $pessoa['codpes'];
                break;
            case 'username':
                $username = config('web-ldap-admin.prefixUsername') . $pessoa['codpes'];
                if (config('web-ldap-admin.prefixUsername') != '') {
                    $attr['employeeNumber'] = $pessoa['codpes'];
                } else {
                    $attr['employeeNumber'] = '';
                }
                $attr['physicalDeliveryOfficeName'] = $pessoa['codpes'];
                break;
            default:
                $username = $pessoa['codpes'];
                $attr['physicalDeliveryOfficeName'] = $pessoa['codpes']; # por padrão gravar o codpes na coluna Office do MS AD // TODO quem usa Samba precisa testar
                $attr['employeeNumber'] = '';
                break;
        }

        // setando para testes se não vier dtanas
        if (!isset($pessoa['dtanas'])) {
            $pessoa['dtanas'] = '1/1/1970';
        }

        // setando senha
        switch (config('web-ldap-admin.senhaPadrao')) {
            case 'random':
                $password = Utils::senhaAleatoria();
                break;

            case 'data_nascimento':
            default:
                $password = ($pessoa['dtanas'] != '') ? date('dmY', strtotime($pessoa['dtanas'])) : Utils::senhaAleatoria();
        }

        if ($pessoa['nomabvset']) {
            // o setor é o vínculo estendido + setor (sem o código da unidade)
            $setor = $pessoa['tipvinext'] . ' ' . explode('-', $pessoa['nomabvset'])[0];
        } else {
            $setor = $pessoa['tipvinext'];
            if ($pessoa['tipvinext'] == 'Aluno de Graduação') {
                if (empty(config('web-ldap-admin.grCursoSetor'))) {
                    $nomabvset = Graduacao::setorAluno($pessoa['codpes'], config('web-ldap-admin.replicado_unidade'))['nomabvset'];
                } else {
                    $curso = Graduacao::curso($pessoa['codpes'], config('web-ldap-admin.replicado_unidade'));
                    $codcur = $curso['codcur'];
                    $codhab = $curso['codhab'];
                    foreach (config('web-ldap-admin.grCursoSetor') as $grCursoSetor) {
                        if ($grCursoSetor['codcur'] == $codcur && $grCursoSetor['codhab'] == $codhab) {
                            $nomabvset = $grCursoSetor['nomabvset'];
                        }
                    }
                }
                $setor = (isset($nomabvset)) ? trim($pessoa['tipvinext'] . ' ' . $nomabvset) : $pessoa['tipvinext'];
            }
            // aqui poderia tratar os outros casos de Pós Graduação, Posdoc, etc
        }
        $attr['setor'] = $setor;

        $attr['nome'] = $pessoa['nompesttd'] ?? $pessoa['nompes'];
        $attr['email'] = $pessoa['codema'];

        $attr['descricao'] = 'Sincronizado com o replicado';

        if( $pessoa['tipvinext'] != 'Externo') {
            if(config('web-ldap-admin.tipoNomesGrupos') == 'extenso'){
                $vinculosSetores = \App\Replicado\Pessoa::listarVinculosExtensoSetores($pessoa['codpes'], config('web-ldap-admin.replicado_unidade'));
                foreach ($vinculosSetores as $key => $value) {
                    if ($value == 'Aluno de Graduação' && isset($nomabvset)) {
                        $vinculosSetores[1] = 'Aluno de Graduação ' . $nomabvset;
                    }
                }
                $grupos = ($pessoa['tipvinext'] != 'Externo') ? $vinculosSetores : [$pessoa['tipvinext']];
            }
            if(config('web-ldap-admin.tipoNomesGrupos') == 'siglas'){
                $setores = Pessoa::obterSiglasSetoresAtivos($pessoa['codpes']);
                $vinculos = Pessoa::obterSiglasVinculosAtivos($pessoa['codpes']);
                $grupos = array_merge($setores,$vinculos);
            }
        } else {
            $grupos = [$pessoa['tipvinext']];
        }

        // Se a sincronização dos grupos com o replicado for desativada, vamos mandar esse array vazio
        if (config('web-ldap-admin.syncGroupsWithReplicado') == 'no') {
            $grupos = [];
        }

        $grupos = array_unique($grupos);
        sort($grupos);

        self::createOrUpdate($username, $attr, $grupos, $password);
    }
}
