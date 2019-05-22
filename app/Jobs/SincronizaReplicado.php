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
        $this->sync(Pessoa::docentes($this->unidade),'docentes');

        // Sicroniza funcionári@s
        //$this->sync(Pessoa::servidores($this->unidade),'servidores');

        // Sicroniza estagiarios
        //$this->sync(Pessoa::estagiarios($this->unidade),'estagiarios');

        // Sicroniza designados
        //$this->sync(Pessoa::designados($this->unidade),'designados');

        // Sicroniza alunos
        //$this->sync(Graduacao::ativos($this->unidade),'graduacao');
        //$this->sync(Posgraduacao::ativos($this->unidade),'pos');

    }

    public function sync($pessoas,$grupo)
    {
        if($pessoas){
            foreach($pessoas as $pessoa) { 
                LdapUser::createOrUpdate($pessoa['codpes'], [
                    'nome' => $pessoa['nompes'],
                    'email' => $pessoa['codema']
                ],
                ['TESTE']);
            }
        }
    }
}
