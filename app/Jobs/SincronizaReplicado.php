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

    private $unidade;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->unidade = env('REPLICADO_UNIDADE');  
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        // Sicroniza docentes
        $this->sync(Pessoa::docentesAtivos($this->unidade),'docentes');

        // Sicroniza alunos
        $this->sync(Graduacao::ativos($this->unidade),'graduacao');
        $this->sync(Posgraduacao::ativos($this->unidade),'pos');

        // Sicroniza funcionári@s
        $this->sync(Pessoas::servidoresAtivos($this->unidade),'servidores');

    }

    public function sync($pessoas,$grupo)
    {
        foreach($pessoas as $pessoa) {
            LdapUser::createOrUpdate($pessoa['codpes'], [
                'nome' => $pessoa['nompes'],
                'email' => $pessoa['codema']
            ],
            $grupo);
        }
    }
}
