<?php

namespace App\Ldap;

use Adldap\Laravel\Facades\Adldap;

class Group
{
    public static function createOrUpdate(string $name)
    {
        $group = Adldap::search()->groups()->where('cn', '=', $name)->first();

        if (is_null($group) || $group == false) {
            $group = Adldap::make()->group();

            // define DN para esse grupo
            $dn = "CN={$name},cn=Users," . $group->getDnBuilder();
            $group->setDn($dn);
            $group->setAccountName(trim($name));
            // save
            $group->save();

            // Busca a OU padrão informada no .env
            $ou = Adldap::search()->ous()->find(config('web-ldap-admin.ouDefault'));
            // Move o grupo para a OU padrão somente se ela existir,
            // do contrário deixa o grupo na raiz
            $group->move($ou);
        }

        return $group;
    }

    /**
     * Adiciona usuários à grupos, cria grupo se necessário
     *
     * @param \Adldap\Models\User $user
     * @param Array $group
     * @return Null
     */
    public static function addMember(\Adldap\Models\User $user, array $groups)
    {
        $ldap_user = Adldap::search()->users()->where('cn', '=', $user->getName())->first();
        $ldap_user = $user;

        $before_groups = $ldap_user->getGroupNames();
        $notRemoveGroups = explode(',', config('web-ldap-admin.notRemoveGroups'));
        $keep_groups = array_intersect($before_groups, $notRemoveGroups);

        $groups = array_merge($keep_groups, $groups);

        // Vamos remover todos grupos e adicionar apenas os necessários
        $ldap_user->removeAllGroups();

        //remove posições vazias, repetidas e sujas
        $groups = array_map('trim', $groups);
        $groups = array_filter($groups);
        $groups = array_unique($groups);

        foreach ($groups as $groupname) {
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
        foreach ($groups as $group) {
            //if(empty(trim($group->getDescription()))){
            array_push($r, $group->getName());
            //}
        }
        sort($r);
        return $r;
    }
}
