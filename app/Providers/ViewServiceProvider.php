<?php

namespace App\Providers;

use App\View\Composers\ProfileComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Using class based composers...
        View::composer('profile', ProfileComposer::class);

        // Using closure based composers...
        View::composer('*', function ($view) {
            // Menu dinâmico solicita conta admin
            $menuContaAdmin = [
                'text' => 'Solicitação de Conta de Administrador',
                'url' => 'solicita',
                'can' => 'ninguem',
            ];

            if (config('web-ldap-admin.solicitaContaAdmin') == 1) {
                $menuContaAdmin['can'] = 'user';
                \UspTheme::addMenu('solicitaContaAdmin', $menuContaAdmin);
            } elseif (config('web-ldap-admin.solicitaContaAdmin') == 2) {
                $menuContaAdmin['can'] = 'servidor';
                \UspTheme::addMenu('solicitaContaAdmin', $menuContaAdmin);
            }

            // menu dinâmico badge indicando syncronização de login ativa
            if (config('web-ldap-admin.sincLdapLogin') == 1) {
                $text = '<span class="badge badge-info">on <i class="fas fa-handshake"></i></span>';
                $title = 'Sincronização automática ativada';
            } else {
                $text = '<span class="badge badge-dark"> off <i class="fas fa-handshake-alt-slash"></i></span>';
                $title = 'Sincronização automática desativada';
            }
            \UspTheme::addMenu('web-ldap-admin', [
                'text' => $text,
                'title' => $title,
                'can' => 'gerente',
            ]);

        });

    }
}
