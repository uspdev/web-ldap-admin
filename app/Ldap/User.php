<?php

namespace App\Ldap;

use Adldap\Laravel\Facades\Adldap;

class User
{
    public static function createOrUpdate(String $username)
    {
        $user = Adldap::search()->users()->find($username);
        //$user = Adldap::search()->users()->find('thiago');
        if(is_null($user)){
            $user = Adldap::make()->user([
                'cn' => $username,
            ]);
        }
        $user->cn = $username;
        //echo "<pre>"; var_dump($user); die();
        $user->setDisplayName('Fulano');
        $user->setFirstName('Fulano');
        $user->setLastName('Verissimo');

        $user->setEmail('a@kdkd.ff.f');
        //echo "<pre>"; var_dump($user); die();
        $user->save();

/*
                // atualiza alguns atríbutos
                $name = trim($logado->name);
                $name_array = explode(' ',$name);
                $firstName = array_shift($name_array);
                $lastName = implode(' ',$name_array);

                $user->setDisplayName($name);
                $user->setFirstName($firstName);
                $user->setLastName($lastName);

                $user->setHomeDrive(env('LDAP_HOMEDRIVE') . ':');
                $user->setHomeDirectory('\\\\'. env('LDAP_SERVERFILE'). '\\' . $logado->id);
                $user->setEmail($logado->email);
                $user->save();

                // retorna alguns atributos    
                $attr['display_name'] = $user->getDisplayName();

                $attr['email'] = $user->getEmail();

                $last = $user->getPasswordLastSetDate();
                if(!is_null($last)) { 
                    $last = Carbon::createFromFormat('Y-m-d H:i:s', $last)->format('d/m/Y');
                }
                $attr['senha_alterada_em'] = $last;

                $attr['grupos'] = $user->getGroupNames();
            
                $attr['quota'] = round($user->quota[0]/1024,2);
            
                $expira = $user->expirationDate();
                if(!is_null($expira)) {
                    $expira = Carbon::instance($expira)->format('d/m/Y');
                }
                $attr['expira'] = $expira;

                $attr['drive'] = $user->getHomeDrive();

                $attr['dir'] = $user->getHomeDirectory();
           
                $ativacao = $user->whencreated[0];
                if(!is_null($ativacao)) {
                    $ativacao = Carbon::createFromFormat('YmdHis\.0\Z', $ativacao)->format('d/m/Y');
                }
                $attr['ativacao'] = $ativacao;
            }
*/
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
