<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use Adldap\Laravel\Facades\Adldap;

class LdapEmailRule implements Rule
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
        $users = Adldap::search()->users()->get();
        $emails = [];

        foreach ($users as $user) {
           array_push($emails,$user->getEmail());
        }
        return !in_array($value, $emails);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Email já utilizado no ldap, escolha outro';
    }
}
