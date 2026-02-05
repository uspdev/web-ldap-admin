<?php

namespace App\Ldap;

use LdapRecord\Models\ActiveDirectory\Group as LdapGroupModel;
use LdapRecord\Models\ActiveDirectory\OrganizationalUnit;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class Group
{
    public static function createOrUpdate(string $name)
    {
        // invocado por:
        //     no login (LoginListener::handle -> User::criarOuAtualizarPorArray -> User::createOrUpdate -> Group::addMember)
        //     menu "Solicitação de Conta de Administrador" -> botão "Enviar" (SolicitaController::store)
        //     menu "Usuários Ldap" -> algum usuário Ldap, menu "Minha Conta (trocar senha da rede)" -> botão "Grupo" -> botão "Salvar" (LdapUserController::addGroup)
        //     menu "Criar usuário" -> botão "Enviar Dados" (LdapUserController::store -> User::createOrUpdate -> Group::addMember)
        //     menu "Criar usuário" -> botão "Enviar Dados" (LdapUserController::store -> User::criarOuAtualizarPorArray -> User::createOrUpdate -> Group::addMember)
        //     menu "Sincronizar ..." -> botão "Sincronizar com replicado" -> botão "Sincronizar" (SincronizaReplicado::handle::sync -> User::criarOuAtualizarPorArray -> User::createOrUpdate -> Group::addMember)
        //     menu "Sincronizar ..." -> botão "Sincronizar com replicado" -> botão "Sincronizar" (SincronizaReplicado::handle::sync -> User::desativarUsers -> Group::addMember)
        //     a cada 12 horas (RevokeLocalAdminGroupJob::handle)
        
        $name = trim($name);
        $group = LdapGroupModel::where('cn', '=', $name)->first();
        if (!$group) {
            $group = new LdapGroupModel();
            $group->cn = $name;
            // vamos prefixar o nome do grupo de forma a não conflitar
            $group->sAMAccountName = 'GRUPO-' . $name;

            # Deixando essa linha temporariamente desativada pois está gerando o erro no login:
            # ldap_modify_batch(): Batch Modify: Invalid DN syntax at
            #$group->setAttribute('info', 'Criado por web-ldap-admin em ' . now()->format('d/m/Y H:i:s'));

            $group->save();

            // Move o grupo para a OU padrão somente se ela existir,
            // do contrário deixa o grupo na raiz ou no local de origem
            // Se vazio, não é necessário alterar nada, pois o default é a raiz (Thiago)
            if (config('web-ldap-admin.ouDefault') != '') {
                $ou = OrganizationalUnit::findBy('ou', config('web-ldap-admin.ouDefault'));
                if ($ou) {
                    $group->move($ou);
                }
            }

        }

        return $group;
    }

    /**
     * Adiciona usuários à grupos, cria grupo se necessário
     *
     * @param \LdapRecord\Models\User $user
     * @param Array $group
     * @return Null
     */
    public static function addMember(LdapUser $user, array $groups)
    {
        // invocado por:
        //     no login (LoginListener::handle -> User::criarOuAtualizarPorArray -> User::createOrUpdate)
        //     menu "Criar usuário" -> botão "Enviar Dados" (LdapUserController::store -> User::createOrUpdate)
        //     menu "Criar usuário" -> botão "Enviar Dados" (LdapUserController::store -> User::criarOuAtualizarPorArray -> User::createOrUpdate)
        //     menu "Sincronizar ..." -> botão "Sincronizar com replicado" -> botão "Sincronizar" (SincronizaReplicado::handle::sync -> User::criarOuAtualizarPorArray -> User::createOrUpdate)
        //     menu "Sincronizar ..." -> botão "Sincronizar com replicado" -> botão "Sincronizar" (SincronizaReplicado::handle::sync -> User::desativarUsers)

        $before_groups = $user->groups()->get()->pluck('cn')->flatten()->toArray();
        $notRemoveGroups = explode(',', config('web-ldap-admin.notRemoveGroups'));
        $keep_groups = array_intersect($before_groups, $notRemoveGroups);

        $groups = array_merge($keep_groups, $groups);

        if (config('web-ldap-admin.removeAllGroups') == 'yes') {
            $user->groups()->detach($user->groups()->get());
        }

        //remove posições vazias, repetidas e sujas
        $groups = array_map('trim', $groups);
        $groups = array_filter($groups);
        $groups = array_unique($groups);

        foreach ($groups as $groupname) {
            $group = self::createOrUpdate($groupname);
            // if(!in_array($user->getFirstAttribute('samaccountname'), $group->getMemberNames())){
            //     $group->addMember($user);
            // }           
            // comentado pois dá erro no login
            // no SET não usamos o login da pessoa então OK
            if (!$user->groups()->exists($group)) {
                $group->members()->attach($user);
            }
        }
    }

    public static function listaGrupos()
    {
        // invocado por:
        //     menu "Usuários Ldap" (LdapUserController::index)
        //     menu "Usuários Ldap" -> algum usuário Ldap, menu "Minha Conta (trocar senha da rede)" (LdapUserController::show, my -> ldapusers.partials.add-group-form)
        //     menu "Criar usuário" (LdapUserController::create -> ldapusers.partials.create-form)

        // Nota: não encontrei nada que me permitisse distinguir grupo do default do sistema ou não
        // assim, por hora, vou assumir que os grupos criado pelo laravel estão sem descrição
        // adicionando iscriticalsystemobject como filtro. Melhora mas não limpa todos (Masaki)
        $r = [];
        $groups = LdapGroupModel::where('iscriticalsystemobject', '!=', 'TRUE')->get();
        foreach ($groups as $group) {
            array_push($r, $group->cn[0] ?? $group->name[0]);
        }
        sort($r);
        return $r;
    }
}
