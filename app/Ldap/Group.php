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
    public static function addMember($user, $groupname)
    {
        $group = Group::createOrUpdate($groupname);
        foreach ($group->getMemberNames() as $name) {
            if($name == $user->getName()){
                return true;
            }
        }
        $group->addMember($user);
    }
}
