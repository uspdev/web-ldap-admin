<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Uspdev\Replicado\Pessoa;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::resource('ldapusers', 'App\Policies\LdapUserPolicy');

        // gate para servidor
        Gate::define('servidor', function ($servidor) {
            $vinculos = Pessoa::obterSiglasVinculosAtivos(Auth::user()->codpes);
            if($vinculos == null) $vinculos = [];

            // correção de bug prévio à atualização para Laravel 12: para funcionar corretamente, faltava aplicar o trim aqui
            $vinculos = array_map('trim', $vinculos);

            return in_array('SERVIDOR', $vinculos);
        });
    }
}
