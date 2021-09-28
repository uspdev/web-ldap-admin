<?php

namespace App\Ldap;

use Adldap\Laravel\Facades\Adldap;
use Carbon\Carbon;

class Group
{
    public static function createOrUpdate(string $name)
    {
        $group = Adldap::search()->groups()->where('cn','=',$name)->first();
        
        if (is_null($group) || $group == false) {
            $group = Adldap::make()->group();

            // define DN para esse user
            $dn = "CN={$name}," .  $group->getDnBuilder();
            $group->setDn($dn);
            $group->setAccountName(trim($name));
            $group->save();
        }

        // save
        return $group;
    }

    // recebe instâncias
    public static function addMember($user, $groups)
    {
        $ldap_user = Adldap::search()->users()->where('cn','=',$user->getName())->first();

        // Vamos remover todos grupos e adicionar apenas os necessários
        $ldap_user->removeAllGroups();

        //remove posições vazias
        $groups = array_filter($groups);

        foreach($groups as $groupname) {
            $group = self::createOrUpdate($groupname);
            $group->addMember($user);
            $group->save();
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
        sort($r);
        return $r;
    }
}
