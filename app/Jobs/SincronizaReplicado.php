<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Uspdev\Replicado\Pessoa;
use Uspdev\Replicado\Graduacao;
use Uspdev\Replicado\Posgraduacao;

use App\Ldap\User as LdapUser;
use App\Ldap\Group as LdapGroup;
use Adldap\Laravel\Facades\Adldap;

class SincronizaReplicado implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        // Sicroniza docentes
        $pessoas = Pessoa::docentesAtivos(8);
        foreach($pessoas as $pessoa) {
            LdapUser::createOrUpdate($pessoa['codpes'], [
                'nome' => $pessoa['nompes'],
                'email' => $pessoa['codema']
            ],
            'docentes');
        }

        // Sicroniza alunos de graduação
        $pessoas = Graduacao::ativos(8);
        foreach($pessoas as $pessoa) {
            LdapUser::createOrUpdate($pessoa['codpes'], [
                'nome' => $pessoa['nompes'],
                'email' => $pessoa['codema']
            ],
            'graduacao');
        }

        // Sicroniza alunos de pós-graduacao
        $pessoas = Posgraduacao::ativos(8);
        foreach($pessoas as $pessoa) {
            LdapUser::createOrUpdate($pessoa['codpes'], [
                'nome' => $pessoa['nompes'],
                'email' => $pessoa['codema']
            ],
            'pos');
        }
    }
}
