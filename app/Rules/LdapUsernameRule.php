<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use Adldap\Laravel\Facades\Adldap;

class LdapUsernameRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $user = Adldap::search()->users()->where('cn', '=', $value)->first();

        return empty($user);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Esse usuário já existe no ldap';
    }
}
