<?php

namespace App\Policies;

use App\User;
use Ldap\User as LdapUser;
use Illuminate\Auth\Access\HandlesAuthorization;

use Illuminate\Support\Facades\Gate;
use Auth;

class LdapUserPolicy
{
    use HandlesAuthorization;
    
    public $is_superAdmin;

    public function __construct()
    {
        $this->is_superAdmin = Gate::allows('admin');
    }

    /**
     * Determine whether the user can view the ldap user.
     *
     * @param  \App\User  $user
     * @param  \App\LdapUser  $ldapUser
     * @return mixed
     */
    public function view(User $user, $id)
    {
        $owner = $user->username_senhaunica === $id;
        return $owner || $this->is_superAdmin ;
    }

    /**
     * Determine whether the user can create ldap users.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the ldap user.
     *
     * @param  \App\User  $user
     * @param  \App\LdapUser  $ldapUser
     * @return mixed
     */
    public function update(User $user, $id)
    {
        $owner = $user->username_senhaunica === $id;
        return $owner || $this->is_superAdmin ;
    }

    /**
     * Determine whether the user can delete the ldap user.
     *
     * @param  \App\User  $user
     * @param  \App\LdapUser  $ldapUser
     * @return mixed
     */
    public function delete(User $user)
    {
        //
    }

    /**
     * Determine whether the user can restore the ldap user.
     *
     * @param  \App\User  $user
     * @param  \App\LdapUser  $ldapUser
     * @return mixed
     */
    public function restore(User $user)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the ldap user.
     *
     * @param  \App\User  $user
     * @param  \App\LdapUser  $ldapUser
     * @return mixed
     */
    public function forceDelete(User $user)
    {
        //
    }
}
