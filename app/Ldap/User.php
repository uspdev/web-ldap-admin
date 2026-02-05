<?php

namespace App\Ldap;

use App\Ldap\Group as LdapGroup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Uspdev\Replicado\Graduacao;
use Uspdev\Replicado\Estrutura;
use Uspdev\Replicado\Pessoa;
use Uspdev\Utils\Generic as Utils;

use LdapRecord\Models\ActiveDirectory\Container;
use LdapRecord\Models\ActiveDirectory\Group as LdapGroupModel;
use LdapRecord\Models\ActiveDirectory\OrganizationalUnit;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use LdapRecord\Models\Attributes\AccountControl;

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
        // invocado por:
        //     no login (LoginListener::handle -> User::criarOuAtualizarPorArray)
        //     menu "Criar usuário" -> botão "Enviar Dados" (LdapUserController::store)
        //     menu "Criar usuário" -> botão "Enviar Dados" (LdapUserController::store -> User::criarOuAtualizarPorArray)
        //     menu "Sincronizar ..." -> botão "Sincronizar com replicado" -> botão "Sincronizar" (SincronizaReplicado::handle::sync -> User::criarOuAtualizarPorArray)

        // vamos ver se o usuário já existe
        $user = SELF::obterUserPorUsername($username);

        # Novo usuário
        if (is_null($user) or $user == false) {
            $user = new LdapUser();
            
            // define DN para esse user
            $baseDn = $user->getConnection()->getConfiguration()->get('base_dn');
            $user->setDn("CN={$username},CN=Users,{$baseDn}");

            // se não for fornecido senha vamos gerar aleatório forte
            $user->unicodepwd = $password ?? Utils::senhaAleatoria();

            // Trocar a senha no próximo logon
            if (config('web-ldap-admin.obrigaTrocarSenhaNoWindows')) {
                $user->pwdlastset = 0;
            }

            // Enable the new user (using user account control).
            $user->useraccountcontrol = AccountControl::NORMAL_ACCOUNT;

            // vamos expirar senha conforme config
            $user->accountExpires = SELF::getExpiryDays();
        }

        // login no windows
        $user->samaccountname = $username;
        // nome de exibição
        $user->displayname = $attr['nome'];

        // atribuindo nome e sobrenome
        $nome_array = explode(' ', $attr['nome']);
        if (count($nome_array) > 1) {
            $user->givenname = trim($nome_array[0]);
            unset($nome_array[0]);
            $user->sn = implode(' ', $nome_array);
        } else {
            $user->givenname = trim($nome_array[0]);
        }

        if (!empty($attr['email'])) {
            $user->mail = $attr['email'];
        }

        // caso o codpes venha no employeenumber
        if (!empty($attr['employeeNumber'])) {
            $user->employeeNumber = $attr['employeeNumber'];
        }

        // Departamento
        if (!empty($attr['setor'])) {
            $user->department = $attr['setor'];
        }

        // Descrição, informa se a conta foi criada a partir da sincronização
        if (!empty($attr['descricao'])) {
            $user->description = $attr['descricao'];
        }

        // Atributos para Linux
        $username_integer = (int) $username;
        if(config('web-ldap-admin.usarAtributosLinux') && $username_integer!=0) {  
            
            $user->uid = config('web-ldap-admin.prefixo_linux') . $username;
            $user->uidNumber = $username;
            $user->gidNumber = config('web-ldap-admin.gid_linux');
            $user->loginShell = '/bin/bash';
            $user->userPrincipalName = $username . '@' . config('web-ldap-admin.ldap_domain');
            $user->unixHomeDirectory = '/home/' . config('web-ldap-admin.prefixo_linux') . $username;
        }

        $user->save();

        $user->department = $attr['setor'];

        // Adiciona a um ou mais grupo
        LdapGroup::addMember($user, $groups);

        // Busca a OU padrão informada no .env
        // Se vazio, não é necessário alterar nada, pois o default é a raiz (Thiago)
        if(config('web-ldap-admin.ouDefault') != ''){
            $ou = OrganizationalUnit::findBy('ou', config('web-ldap-admin.ouDefault'));

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
        // invocado por:
        //     no login (LoginListener::handle -> User::criarOuAtualizarPorArray -> User::createOrUpdate)
        //     menu "Criar usuário" -> botão "Enviar Dados" (LdapUserController::store -> User::createOrUpdate)
        //     menu "Criar usuário" -> botão "Enviar Dados" (LdapUserController::store -> User::criarOuAtualizarPorArray -> User::createOrUpdate)
        //     menu "Sincronizar ..." -> botão "Sincronizar com replicado" -> botão "Sincronizar" (SincronizaReplicado::handle::sync -> User::criarOuAtualizarPorArray -> User::createOrUpdate)

        if (config('web-ldap-admin.expirarEm') == 0) {
            return null;
        } else {
            return now()->addDays((int) config('web-ldap-admin.expirarEm'));
        }
    }

    /**
     * Define prazo de expiração para senha da conta
     */
    public static function expirarSenha($username, $expiry)
    {
        // invocado por:
        //     menu "Usuários Ldap" -> algum usuário Ldap, menu "Minha Conta (trocar senha da rede)" -> alguma opção do menu de expiração de conta, alguma opção do menu de habilitar/desabilitar, botão "Alterar" (LdapUserController::update)

        $user = SELF::obterUserPorUsername($username);
        if ($user) {
            if ($expiry) {
                $user->accountExpires = now()->addDays((int) $expiry);
            } else {
                $user->accountExpires = 0;
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
     * @return LdapRecord\Models\ActiveDirectory\User
     */
    public static function obterUserPorCodpes($codpes)
    {
        // invocado por:
        //     menu "Minha Conta (trocar senha da rede)" (LdapUserController::my)
        //     menu "Criar usuário" -> botão "Enviar Dados" (LdapUserController::store)

        $user = LdapUser::findBy(config('web-ldap-admin.campoCodpes'), $codpes);

        // não vai encontrar se for pelo username, nesse caso vamos usar o CN
        if (is_null($user)) {
            $user = LdapUser::where('cn', '=', $codpes)->first();
        }

        return $user;
    }

    /**
     * Obtém uma instância de usuário com busca pelo username
     *
     * @param String $username
     * @return LdapRecord\Models\ActiveDirectory\User
     */
    public static function obterUserPorUsername($username)
    {
        // invocado por:
        //     no login (LoginListener::handle -> User::criarOuAtualizarPorArray -> User::createOrUpdate)
        //     menu "Solicitação de Conta de Administrador" -> botão "Enviar" (SolicitaController::store)
        //     menu "Usuários Ldap" -> algum usuário Ldap (LdapUserController::show)
        //     menu "Usuários Ldap" -> algum usuário Ldap, menu "Minha Conta (trocar senha da rede)" -> botão "Grupo" -> botão "Salvar" (LdapUserController::addGroup)
        //     menu "Usuários Ldap" -> algum usuário Ldap, menu "Minha Conta (trocar senha da rede)" -> alguma opção do menu de expiração de conta, alguma opção do menu de habilitar/desabilitar, botão "Alterar" (LdapUserController::update -> User::expirarSenha)
        //     menu "Usuários Ldap" -> algum usuário Ldap, menu "Minha Conta (trocar senha da rede)" -> alguma opção do menu de expiração de conta, alguma opção do menu de habilitar/desabilitar, botão "Alterar" (LdapUserController::update -> User::enable)
        //     menu "Usuários Ldap" -> algum usuário Ldap, menu "Minha Conta (trocar senha da rede)" -> alguma opção do menu de expiração de conta, alguma opção do menu de habilitar/desabilitar, botão "Alterar" (LdapUserController::update -> User::disable)
        //     menu "Usuários Ldap" -> algum usuário Ldap, menu "Minha Conta (trocar senha da rede)" -> alguma opção do menu de expiração de conta, alguma opção do menu de habilitar/desabilitar, botão "Alterar" (LdapUserController::update -> User::changePassword)
        //     menu "Usuários Ldap" -> algum usuário Ldap, menu "Minha Conta (trocar senha da rede)" -> botão "Excluir" (LdapUserController::destroy -> User::delete)
        //     menu "Criar usuário" -> botão "Enviar Dados" (LdapUserController::store -> User::createOrUpdate)
        //     menu "Criar usuário" -> botão "Enviar Dados" (LdapUserController::store -> User::criarOuAtualizarPorArray -> User::createOrUpdate)
        //     menu "Sincronizar ..." -> botão "Sincronizar com replicado" -> botão "Sincronizar" (SincronizaReplicado::handle::sync -> User::criarOuAtualizarPorArray -> User::createOrUpdate)
        //     menu "Sincronizar ..." -> botão "Sincronizar com replicado" -> botão "Sincronizar" (SincronizaReplicado::handle::sync -> User::desativarUsers)
        //     menu "Sincronizar ..." -> botão "Sincronizar com replicado" -> botão "Sincronizar" (SincronizaReplicado::handle::sync -> User::desativarUsers -> User::disable)

        return LdapUser::where('cn', '=', $username)->first();
    }

    /**
     * Coleta atributos do usuário para serem mostrados
     *
     * @param LdapRecord\Models\ActiveDirectory\User $user
     * @return Array
     */
    public static function show(LdapUser $user)
    {
        // invocado por:
        //     menu "Usuários Ldap" -> algum usuário Ldap, menu "Minha Conta (trocar senha da rede)" (LdapUserController::show, my)

        $attr = [];

        // Nome e email
        $attr['username'] = $user->getFirstAttribute('samaccountname');
        $attr['display_name'] = $user->getFirstAttribute('displayname');
        $attr['email'] = $user->getFirstAttribute('mail');
        $attr['description'] = $user->getFirstAttribute('description');
        $attr['codpes'] = SELF::obterCodpes($user);

        // Data da criação da conta
        $ativacao = ldapToCarbon($user, 'whencreated');
        if (!is_null($ativacao)) {
            // vamos subtrair 3 horas para bater com o TZ local. TODO: alguma forma de melhorar isso?
            $ativacao = $ativacao->subHours(3)->format('d/m/Y H:i:s');
        }
        $attr['ativacao'] = $ativacao;

        // data da última senha alterada
        $last = ldapToCarbon($user, 'pwdlastset');
        if (!is_null($last)) {
            $last = $last->format('d/m/Y H:i:s');
        }
        $attr['senha_alterada_em'] = $last;

        // Data da expiração da conta
        $expira = ldapToCarbon($user, 'accountexpires');
        if (!is_null($expira)) {
            $expira = $expira->format('d/m/Y');
        }
        $attr['expira'] = $expira;

        // Grupos: vamos ocultar o grupo "Domain Users"
        $grupos = array_diff($user->groups()->get()->pluck('cn')->flatten()->toArray(), ['Domain Users']);
        sort($grupos);
        $attr['grupos'] = implode(', ', $grupos);

        // Department
        $attr['department'] = $user->getFirstAttribute('department');

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
     * @param LdapRecord\Models\ActiveDirectory\User $user
     * @param $status Se true retorna se o codpes veio do campo correto ou não, segundo o config
     * @return Int|Array|Null
     */
    public static function obterCodpes(LdapUser $user, Bool $status = false)
    {
        // invocado por:
        //     menu "Usuários Ldap" (LdapUserController::index -> ldapusers.index)
        //     menu "Usuários Ldap" -> algum usuário Ldap, menu "Minha Conta (trocar senha da rede)" (LdapUserController::show, my)
        //     menu "Usuários Ldap" -> algum usuário Ldap, menu "Minha Conta (trocar senha da rede)" (LdapUserController::show, my -> User::show)

        $valido = true;
        switch (strtolower(config('web-ldap-admin.campoCodpes'))) {
            case 'employeenumber':
                if (!is_numeric($codpes = $user->getFirstAttribute('employeenumber'))) {
                    $codpes = $user->getFirstAttribute('samaccountname');
                    $valido = false;
                }
                break;
            case 'username':
            default:
                if (!is_numeric($codpes = $user->getFirstAttribute('samaccountname'))) {
                    $codpes = $user->getFirstAttribute('employeenumber');
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
        // invocado por:
        //     menu "Usuários Ldap" -> algum usuário Ldap, menu "Minha Conta (trocar senha da rede)" -> botão "Excluir" (LdapUserController::destroy)

        $user = SELF::obterUserPorUsername($username);
        if (!is_null($user)) {
            $user->delete();
            return true;
        }
        return false;
    }

    public static function disable(String $username)
    {
        // invocado por:
        //     menu "Usuários Ldap" -> algum usuário Ldap, menu "Minha Conta (trocar senha da rede)" -> alguma opção do menu de expiração de conta, alguma opção do menu de habilitar/desabilitar, botão "Alterar" (LdapUserController::update)
        //     menu "Sincronizar ..." -> botão "Sincronizar com replicado" -> botão "Sincronizar" (SincronizaReplicado::handle::sync -> User::desativarUsers)

        $user = SELF::obterUserPorUsername($username);
        if (!is_null($user)) {
            # https://support.microsoft.com/pt-br/help/305144/how-to-use-the-useraccountcontrol-flags-to-manipulate-user-account-pro
            $user->useraccountcontrol = AccountControl::ACCOUNTDISABLE;
            $user->save();
            return true;
        }
        return false;
    }

    public static function enable(String $username)
    {
        // invocado por:
        //     menu "Usuários Ldap" -> algum usuário Ldap, menu "Minha Conta (trocar senha da rede)" -> alguma opção do menu de expiração de conta, alguma opção do menu de habilitar/desabilitar, botão "Alterar" (LdapUserController::update)

        $user = SELF::obterUserPorUsername($username);

        if (!is_null($user)) {
            # https://support.microsoft.com/pt-br/help/305144/how-to-use-the-useraccountcontrol-flags-to-manipulate-user-account-pro
            $user->useraccountcontrol = AccountControl::NORMAL_ACCOUNT;
            $user->save();
            return true;
        }
        return false;
    }

    public static function changePassword($username, String $password, $must_change_pwd = null): bool
    {
        // invocado por:
        //     menu "Usuários Ldap" -> algum usuário Ldap, menu "Minha Conta (trocar senha da rede)" -> alguma opção do menu de expiração de conta, alguma opção do menu de habilitar/desabilitar, botão "Alterar" (LdapUserController::update)

        $user = SELF::obterUserPorUsername($username);
        if (is_null($user)) {
            return false;
        }

        $user->unicodepwd = $password;
        // Leonardo Ruiz: Alteracao de senha nao deve alterar validade da conta
        //$user->setAccountExpiry(SELF::getExpiryDays());

        if ($must_change_pwd) {
            $user->pwdlastset = 0;
        }

        try {
            $user->save();
            $result = true;
        } catch (\Exception $e) {
            echo (Gate::check('gerente')) ? $e->getMessage() : null;
            $result = false;
        }
        return $result;
    }

    public static function getUsersGroup($grupo)
    {
        // invocado por:
        //     menu "Sincronizar ..." -> botão "Sincronizar com replicado" (LdapUserController::syncReplicadoForm)
        //     menu "Sincronizar ..." -> botão "Sincronizar com replicado" -> botão "Sincronizar" (SincronizaReplicado::handle::sync)
        
        $ldapusers = [];
        $group = LdapGroupModel::findBy('cn', $grupo);
        if ($group != false) {
            $ldapusers = LdapUser::query();
            $ldapusers = $ldapusers->where('memberof', '=', $group->getDn());
            foreach (config('web-ldap-admin.ocultarUsuarios') as $ocultar) {
                $ldapusers = $ldapusers->where('samaccountname', '!=', $ocultar);
            }
            $ldapusers = $ldapusers->orderBy('displayname', 'asc');
            $ldapusers = $ldapusers->paginate(config('web-ldap-admin.registrosPorPagina'));
        }

        return $ldapusers;
    }

    public static function desativarUsers($desligados)
    {
        // invocado por:
        //     menu "Sincronizar ..." -> botão "Sincronizar com replicado" -> botão "Sincronizar" (SincronizaReplicado::handle::sync)

        foreach ($desligados as $desligado) {
            // remover dos grupos
            $user = SELF::obterUserPorUsername($desligado);
            $groups = $user->groups()->get();

            foreach ($groups as $group) {
                echo "{$desligado}: <br />";
                $group->members()->detach($user);
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
    public static function criarOuAtualizarPorArray($pessoa, $metodo = '')
    {
        // invocado por:
        //     no login (LoginListener::handle)
        //     menu "Criar usuário" -> botão "Enviar Dados" (LdapUserController::store)
        //     menu "Sincronizar ..." -> botão "Sincronizar com replicado" -> botão "Sincronizar" (SincronizaReplicado::handle::sync)

        // setando username e codpes (similar loginListener)
        switch (strtolower(config('web-ldap-admin.campoCodpes'))) {
            case 'employeenumber':
                $email = Pessoa::retornarEmailUsp($pessoa['codpes']) ?? $pessoa['codema'];
                $email = empty($email) ?: Pessoa::email($pessoa['codpes']);
                $username = explode('@', $email)[0];
                $username = preg_replace("/[^a-zA-Z0-9]+/", "", $username); //email sem caracteres especiais
                $username = substr($username, 0, 15); //limitando em 15 caracteres
                $attr['employeeNumber'] = $pessoa['codpes'];
                // dd($email, $pessoa);
                break;
            case 'username':
            default:
                $username = $pessoa['codpes'];
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
                    try {
                        $nomabvset = Graduacao::setorAluno($pessoa['codpes'], config('web-ldap-admin.replicado_unidade'))['nomabvset'];
                    } catch (\Exception $e) {
                        $nomabvset = 'Sem departamento';
                    }
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
                $vinculosSetores = Pessoa::vinculosSetores($pessoa['codpes'], config('web-ldap-admin.replicado_unidade'));
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
                // caso não haja vinculos ou setores, vamos deixar como array
                if(is_null($setores)) $setores = [];
                if(is_null($vinculos)) $vinculos = [];
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

        // só grava o usuário no servidor LDAP se ele pertencer a vínculos autorizados para tal
        // (tanto no login do usuário quanto na sincronização com o replicado)
        //if ((array_filter(config('web-ldap-admin.vinculos_autorizados')) === []) ||             // quando a variável não foi configurada, permitimos todos os usuários (como sempre havia sido)
        //    !empty(array_intersect($grupos, config('web-ldap-admin.vinculos_autorizados'))))    // só permite se o usuário for de um dos vínculos autorizados
            return self::createOrUpdate($username, $attr, $grupos, $password);
    }

    /**
     * Verifica se a conta está expirada (Retorna true/false)
     */
    public static function getIsExpired($user)
    {
        // invocado por:
        //     menu "Usuários Ldap" (LdapUserController::index -> ldapusers.partials.expiry)

        $expira = ldapToCarbon($user, 'accountexpires');
        return $expira ? $expira->isPast() : false;
    }

    /**
     * Retorna a data de expiração como objeto Carbon ou null
     */
    public static function getExpirationDate($user)
    {
        // invocado por:
        //     menu "Usuários Ldap" (LdapUserController::index -> ldapusers.partials.expiry)

        return ldapToCarbon($user, 'accountexpires');
    }

    /**
     * Retorna um array com os nomes (CN) dos grupos do usuário
     */
    public static function getGroupNames($user)
    {
        // invocado por:
        //     menu "Usuários Ldap" (LdapUserController::index -> ldapusers.index)

        return $user->groups()->get()->pluck('cn')->flatten()->toArray();
    }
}
