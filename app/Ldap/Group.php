<?php

namespace App\Ldap;

use Adldap\Laravel\Facades\Adldap;
use \Adldap\Models\User as LdapUser;

class Group
{
    public static function createOrUpdate(string $name)
    {
        $name = trim($name);
        $group = Adldap::search()->groups()->where('cn', '=', $name)->first();
        if (!$group) {
            $group = Adldap::make()->group();
            $group->setDn("CN={$name}," . $group->getDnBuilder());
            $group->setName($name);
            // vamos prefixar o nome do grupo de forma a não conflitar
            $group->setAttribute('sAMAccountName', 'GRUPO-' . $name);
            $group->setAttribute('info', 'Criado por web-ldap-admin em ' . now()->format('d/m/Y H:i:s'));
            $group->save();

            // Move o grupo para a OU padrão somente se ela existir,
            // do contrário deixa o grupo na raiz ou no local de origem
            $group->move(Adldap::search()->ous()->find(config('web-ldap-admin.ouDefault')));
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
    public static function addMember(LdapUser $user, array $groups)
    {
        $before_groups = $user->getGroupNames();
        $notRemoveGroups = explode(',', config('web-ldap-admin.notRemoveGroups'));
        $keep_groups = array_intersect($before_groups, $notRemoveGroups);

        $groups = array_merge($keep_groups, $groups);

        if (config('web-ldap-admin.removeAllGroups')) {
            $user->removeAllGroups();
        }        

        //remove posições vazias, repetidas e sujas
        $groups = array_map('trim', $groups);
        $groups = array_filter($groups);
        $groups = array_unique($groups);

        foreach ($groups as $groupname) {
            $group = self::createOrUpdate($groupname);
            if (!$user->inGroup($group)) {
                $group->addMember($user);
            }
        }
    }

    public static function listaGrupos()
    {
        // Nota: não encontrei nada que me permissite distinguir grupo do default do sistema ou não
        // assim, por hora, vou assumir que os grupos criado pelo laravel estão sem descrição
        // adicionando iscriticalsystemobject como filtro. Melhora mas não limpa todos (Masaki)
        $r = [];
        $groups = Adldap::search()->groups()->where('iscriticalsystemobject', '!', 'TRUE')->get();
        foreach ($groups as $group) {
            array_push($r, $group->getName());
        }
        sort($r);
        return $r;
    }
}
