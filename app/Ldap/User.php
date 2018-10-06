<?php

namespace App\Ldap;

use Adldap\Laravel\Facades\Adldap;
use Carbon\Carbon;

class User
{
    public static function createOrUpdate(String $username)
    {
        $user = Adldap::search()->users()->find($username);

        if(is_null($user)){
            $user = Adldap::make()->user();

            // define DN para esse user
            $dn = "cn={$username}," .  $user->getDnBuilder();
            $user->setDn($dn);
        }

        // Set the user profile details.
        $user->setAccountName($username); // login no windows

        $user->setDisplayName('dwqd');
        $user->setFirstName('John');
        $user->setLastName('Doe');

        $user->setCompany('ACME');
        $user->setEmail('jdoe@acme.com');

        // atributos para servidor de arquivos 
        //$fileserver = env('LDAP_SERVERFILE');
        //$user->setHomeDrive($fileserver . ':');
        //$user->setHomeDirectory('\\\\'. $fileserver. '\\' . $username);

        // Enable the new user (using user account control).
        $user->setUserAccountControl(512);
        
        // save
        $user->save();
    }
    
    public static function show(String $username)
    {
        $user = Adldap::search()->users()->find($username);

        if(!is_null($user)){
            
            $attr = [];
  
            $attr['display_name'] = $user->getDisplayName();
            $attr['email'] = $user->getEmail();

            $last = $user->getPasswordLastSetDate();
            if(!is_null($last)) { 
                $last = Carbon::createFromFormat('Y-m-d H:i:s', $last)->format('d/m/Y');
            }
            $attr['senha_alterada_em'] = $last;

            $attr['grupos'] = $user->getGroupNames();
            
            $expira = $user->expirationDate();
            if(!is_null($expira)) {
                $expira = Carbon::instance($expira)->format('d/m/Y');
            }
            $attr['expira'] = $expira;

            // filerserver

            //$attr['quota'] = round($user->quota[0]/1024,2);
            //$attr['drive'] = $user->getHomeDrive();
            //$attr['dir'] = $user->getHomeDirectory();
           
            $ativacao = $user->whencreated[0];
            if(!is_null($ativacao)) {
                $ativacao = Carbon::createFromFormat('YmdHis\.0\Z', $ativacao)->format('d/m/Y');
            }
            $attr['ativacao'] = $ativacao;
            
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

        if($user->exists){
            # https://support.microsoft.com/pt-br/help/305144/how-to-use-the-useraccountcontrol-flags-to-manipulate-user-account-pro
            $user->setUserAccountControl(2);
            return true;
        }
        return false;
    }

    public static function changePassword(String $username, String $password)
    {
        $user = Adldap::search()->users()->find($username);
        if(!is_null($user)){
            $user->setPassword($password);
            $user->save();        
        }
    }
}
