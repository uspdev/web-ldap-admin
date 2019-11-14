<?php

namespace App\Ldap;

use Adldap\Laravel\Facades\Adldap;
use Carbon\Carbon;
use App\Ldap\Group as LdapGroup;

class User
{

    /** Estrutura do array attr:
      * $attr['nome']  : Nome completo
      * $attr['email'] : Email
      * $attr['setor'] : Departamento
      **/
    public static function createOrUpdate(string $username, array $attr, array $groups = [])
    {
        $user = Adldap::search()->users()->find($username);

        if (is_null($user) or $user == false) {
            $user = Adldap::make()->user();

            // define DN para esse user
            $dn = "cn={$username}," .  $user->getDnBuilder();
            $user->setDn($dn);
        }

        // Enable the new user (using user account control).
        $user->setUserAccountControl(512);

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
        $user->setDepartment($attr['setor']);

        // save
        $user->save();

        // Adiciona a um grupo
        LdapGroup::addMember($user,$groups);
        
        return $user;
    }
    
    public static function show(String $username)
    {
        $user = Adldap::search()->users()->find($username);
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
            if($user->getUserAccountControl() == 512) {
                $attr['status'] = 'conta ativada'; 
            } else {
                $attr['status'] = 'conta desativada'; 
            }
           
            return $attr;
        }
        return false;
    }

    public static function delete(String $username)
    {
        $user = Adldap::search()->users()->find($username);
        if(!is_null($user)){
            $user->delete();
            return true;
        }
        return false;
    }

    public static function disable(String $username)
    {
        $user = Adldap::search()->users()->find($username);
        if(!is_null($user)){
            # https://support.microsoft.com/pt-br/help/305144/how-to-use-the-useraccountcontrol-flags-to-manipulate-user-account-pro
            $user->setUserAccountControl(2);
            $user->save();
            return true;
        }
        return false;
    }

    public static function enable(String $username)
    {
        $user = Adldap::search()->users()->find($username);

        if(!is_null($user)){
            # https://support.microsoft.com/pt-br/help/305144/how-to-use-the-useraccountcontrol-flags-to-manipulate-user-account-pro
            $user->setUserAccountControl(512);
            $user->save();
            return true;
        }
        return false;
    }

    public static function changePassword($username, String $password)
    {
        // TODO: verificar se a conta está ativada antes de trocar senha
        $user = Adldap::search()->users()->find($username);
        if(!is_null($user)){
            $user->setPassword($password);
            $user->save();        
        }
    }
}
