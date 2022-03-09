<?php

namespace App\Ldap;

use Adldap\Laravel\Facades\Adldap;
use Adldap\Models\Attributes\AccountControl;
use App\Ldap\Group as LdapGroup;
use Carbon\Carbon;

class User
{

    /** Estrutura do array attr:
     * $attr['nome']  : Nome completo
     * $attr['email'] : Email
     * $attr['setor'] : Departamento
     **/
    public static function createOrUpdate(string $username, array $attr, array $groups = [], $password = null)
    {

        $password = $password == null ? 'TrocaXr123!()' : '';

        $user = SELF::obterUserPorUsername($username);

        # Novo usuário
        if (is_null($user) or $user == false) {
            $user = Adldap::make()->user();

            // define DN para esse user
            $user->setDn('cn=' . $username . ',cn=Users,' . $user->getDnBuilder());

            $user->setPassword($password);
            $user->setAttribute('pwdlastset', 0); // Trocar a senha no próximo logon
            $user->setUserAccountControl(AccountControl::NORMAL_ACCOUNT); // Enable the new user (using user account control).
            $user->setAccountExpiry(SELF::getExpiryDays()); // para novos usuários vamos expirar senha conforme config
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

        // caso o codpes venha no telephonenumber
        if (!empty($attr['telephonenumber'])) {
            $user->setTelephoneNumber($attr['telephonenumber']);
        }

        // Departamento
        if (!empty($attr['setor'])) {
            $user->setDepartment($attr['setor']);
        }

        $user->save();

        // Adiciona a um ou mais grupo
        LdapGroup::addMember($user, $groups);

        // Busca a OU padrão informada no .env
        $ou = Adldap::search()->ous()->find(config('web-ldap-admin.ouDefault'));
        // Move o usuário para a OU padrão somente se ela existir,
        // do contrário deixa o usuário na raiz
        $user->move($ou);

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
     */
    public static function obterUserPorCodpes($codpes)
    {
        if (strtolower(config('web-ldap-admin.campoCodpes')) == 'telephonenumber') {
            return Adldap::search()->users()->findBy('telephoneNumber', $codpes);
        }
        if (strtolower(config('web-ldap-admin.campoCodpes') == 'username')) {
            return Adldap::search()->users()->where('cn', '=', $codpes)->first();
        }
        return null;
    }

    /**
     * Obtém uma instância de usuário com busca pelo username
     */
    public static function obterUserPorUsername($username)
    {
        return $user = Adldap::search()->users()->where('cn', '=', $username)->first();
    }

    public static function show($user)
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
            $ativacao = Carbon::createFromFormat('YmdHis\.0\Z', $ativacao)->subHours(3)->format('d/m/Y H:i:s');
        }
        $attr['ativacao'] = $ativacao;

        // última senha alterada
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

        // Grupos
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
     * O codpes pode ser o username ou o telephonenumber e é setado no config.
     * Se não encontrar no campo apropriado faz a busca no outro campo
     * para o caso de ter mudado a regra ao longo do uso
     * 
     * Se não retornar codpes o status pode ser qualquer
     *
     * @param \Adldap\Models\User $user
     * @param $status Se true retorna se o codpes veio do campo correto ou não, segundo o config
     * @return Int|Array|Null
     */
    public static function obterCodpes(\Adldap\Models\User $user, Bool $status = false)
    {
        $valido = true;
        switch (strtolower(config('web-ldap-admin.campoCodpes'))) {
            case 'telephonenumber':
                if (!is_numeric($codpes = $user->getTelephoneNumber())) {
                    $codpes = $user->getAccountName();
                    $valido = false;
                }
                break;
            case 'username':
            default:
                if (!is_numeric($codpes = $user->getAccountName())) {
                    $codpes = $user->getTelephoneNumber();
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

    public static function changePassword($username, String $password): bool
    {
        $user = SELF::obterUserPorUsername($username);
        if (is_null($user)) {
            return false;
        }

        $user->setPassword($password);
        $user->setAccountExpiry(SELF::getExpiryDays());

        try {
            $user->save();
            $result = true;
        } catch (\ErrorException $e) {
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
            $ldapusers = $ldapusers->where('samaccountname', '!=', 'Administrator');
            $ldapusers = $ldapusers->where('samaccountname', '!=', 'krbtgt');
            $ldapusers = $ldapusers->where('samaccountname', '!=', 'Guest');
            $ldapusers = $ldapusers->sortBy('displayname', 'asc');
            $ldapusers = $ldapusers->paginate(config('web-ldap-admin.registrosPorPagina'))->getResults();
        }

        return $ldapusers;
    }

    public static function desativarUsers($desligados)
    {
        foreach ($desligados as $desligado) {
            // remover dos grupos
            $user = SELF::obterUserPorUsername($username);
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

}
