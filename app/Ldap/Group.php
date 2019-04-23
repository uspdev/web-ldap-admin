<?php

namespace App\Ldap;

use Adldap\Laravel\Facades\Adldap;
use Carbon\Carbon;

class Group
{
    public static function createOrUpdate(string $name)
    {
        $group = Adldap::search()->groups()->find($name);

        if(is_null($group)){
            $group = Adldap::make()->group();

            // define DN para esse user
            $dn = "CN={$name}," .  $group->getDnBuilder();
            $group->setDn($dn);
        }

        // save
        //$group->setAttribute('samaccountname', $name);
        $group->setAccountName($name);
        $group->save();
        return $group;
    }

    // recebe instâncias
    public static function addMember($user, $groups)
    {
        foreach($groups as $groupname) {
            if( !is_null($groupname)){
                $group = Group::createOrUpdate($groupname);
                foreach ($group->getMemberNames() as $name) {
                    if($name == $user->getName()){
                        return true;
                    }
                }
                $group->addMember($user);
                $group->save();
            }
        }
    }


    public static function listaGrupos()
    {
        // Nota: não encontrei nada que me permissite distinguir grupo do default do sistema ou não
        // assim, por hora, vou assumir que os grupos criado pelo laravel estão sem descrição
        $r = [];
        $groups = Adldap::search()->groups()->get();
        foreach($groups as $group) {
            if(empty(trim($group->getDescription()))){
                array_push($r,$group->getName()); 
            }
        }
        return $r;
    }
}
