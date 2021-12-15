<?php

namespace App\Ldap;

use Adldap\Laravel\Facades\Adldap;
use Carbon\Carbon;
use App\Ldap\Group as LdapGroup;

use Uspdev\Replicado\Pessoa;
use Adldap\Models\Attributes\AccountControl;

class User
{

    /** Estrutura do array attr:
      * $attr['nome']  : Nome completo
      * $attr['email'] : Email
      * $attr['setor'] : Departamento
      **/
    public static function createOrUpdate(string $username, array $attr, array $groups = [], $password = null)
    {
        $user = Adldap::search()->where('cn', '=', $username)->first();

        # Novo usuário
        if (is_null($user) or $user == false) {
            $user = Adldap::make()->user();

            // define DN para esse user
            $dn = "cn={$username}," .  $user->getDnBuilder();
            $user->setDn($dn);

            // Password
            $user->setPassword($password);

            // Trocar a senha no próximo logon
            $user->setAttribute('pwdlastset', 0);

            // Enable the new user (using user account control).
            $user->setUserAccountControl(AccountControl::NORMAL_ACCOUNT);
        }

        // Set the user profile details.
        $user->setAccountName($username); // login no windows

        // nome
        $user->setDisplayName($attr['nome']);

        $nome_array = explode(' ',$attr['nome']);
        if(count($nome_array)>1) {
            $user->setFirstName(trim($nome_array[0]));
            unset($nome_array[0]);
            $user->setLastName(implode(' ',$nome_array));
        } else {
            $user->setFirstName(trim($nome_array[0]));
        }
        !empty($attr['email'])?$user->setEmail($attr['email']):NULL;

        // Departamento
        if(!empty($attr['setor'])){
            $user->setDepartment($attr['setor']);
        }

        // save
        $user->save();

        // Adiciona a um grupo
        LdapGroup::addMember($user, $groups);

        return $user;
    }

    public static function show(String $username)
    {

        $user = Adldap::search()->where('cn', '=', $username)->first();
        if(!is_null($user)){

            $attr = [];

            // Nome e email
            $attr['username'] = $username;
            $attr['display_name'] = $user->getDisplayName();
            $attr['email'] = $user->getEmail();

            // Data da criação da conta
            $ativacao = $user->whencreated[0];
            if(!is_null($ativacao)) {
                $ativacao = Carbon::createFromFormat('YmdHis\.0\Z', $ativacao)->format('d/m/Y');
            }
            $attr['ativacao'] = $ativacao;

            // última senha alterada
            $last = $user->getPasswordLastSetDate();
            if(!is_null($last)) {
                $last = Carbon::createFromFormat('Y-m-d H:i:s', $last)->format('d/m/Y');
            }
            $attr['senha_alterada_em'] = $last;

            // Data da expiração da conta
            $expira = $user->expirationDate();
            if(!is_null($expira)) {
                $expira = Carbon::instance($expira)->format('d/m/Y');
            }
            $attr['expira'] = $expira;

            // Grupos
            $grupos = array_diff($user->getGroupNames(),['Domain Users']);
            sort($grupos);
            $attr['grupos'] = implode(', ',$grupos);

            // status
            if($user->isEnabled()) {
                $attr['status'] = 'Ativada';
            } else {
                $attr['status'] = 'Desativada';
            }

            // Department
            $attr['department'] = $user->getDepartment();

            return $attr;
        }
        return false;
    }

    public static function delete(String $username)
    {
        $user = Adldap::search()->where('cn', '=', $username)->first();
        if(!is_null($user)){
            $user->delete();
            return true;
        }
        return false;
    }

    public static function disable(String $username)
    {
        $user = Adldap::search()->where('cn', '=', $username)->first();
        if(!is_null($user)){
            # https://support.microsoft.com/pt-br/help/305144/how-to-use-the-useraccountcontrol-flags-to-manipulate-user-account-pro
            $user->setUserAccountControl(AccountControl::ACCOUNTDISABLE);
            // adicionar ao grupo Desativados
            LdapGroup::addMember($user, ['Desativados']);
            $user->save();
            return true;
        }
        return false;
    }

    public static function enable(String $username)
    {
        $user = Adldap::search()->where('cn', '=', $username)->first();

        if(!is_null($user)){
            # https://support.microsoft.com/pt-br/help/305144/how-to-use-the-useraccountcontrol-flags-to-manipulate-user-account-pro
            $user->setUserAccountControl(AccountControl::NORMAL_ACCOUNT);

            // TODO: remover do grupo Desativados
            /*$grupo_desativados = LdapGroup::createOrUpdate('Desativados');
              $grupo_desativados->removeMember($user);

              $group->save();
            */
            $user->save();
            return true;
        }
        return false;
    }

    public static function changePassword($username, String $password) : bool
    {
        $result= true;
        $user = Adldap::search()->where('cn', '=', $username)->whereEnabled()->first();
        if(!is_null($user)){
            $user->setPassword($password);
            
            try {
                $user->save();
            } catch(\ErrorException $e) {
                $result = false;
            }
        }
        return($result);
    }    

    public static function getUsersGroup($grupo)
    {
        $ldapusers = [];
        $group = Adldap::search()->groups()->find($grupo);
        if ($group != false) {
            $ldapusers = Adldap::search()->users();
            $ldapusers = $ldapusers->where('memberof', '=', $group->getDnBuilder()->get());
            $ldapusers = $ldapusers->where('samaccountname','!=','Administrator');
            $ldapusers = $ldapusers->where('samaccountname','!=','krbtgt');
            $ldapusers = $ldapusers->where('samaccountname','!=','Guest');
            $ldapusers = $ldapusers->sortBy('displayname', 'asc');
            $ldapusers = $ldapusers->paginate(config('web-ldap-admin.registrosPorPagina'))->getResults();
        }

        return $ldapusers;
    }

    public static function desativarUsers($desligados)
    {
        foreach ($desligados as $desligado) {
            // remover dos grupos
            $user = Adldap::search()->users()->where('cn', '=', $desligado)->first();
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
