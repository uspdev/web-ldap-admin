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
    private $type;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $type)
    {
        $this->unidade = env('REPLICADO_UNIDADE');  
        $this->type = $type;  
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach($this->type as $type) {
            if($type == 'servidores')
                $this->sync(Pessoa::servidores($this->unidade));

            if($type == 'docentes')
                $this->sync(Pessoa::docentes($this->unidade));

            if($type == 'estagiarios')
                $this->sync(Pessoa::estagiarios($this->unidade));
        }
    }

    public function sync($pessoas)
    {
        if($pessoas){
            foreach($pessoas as $pessoa) {
                $vinculos = Pessoa::vinculosSiglas($pessoa['codpes'],$this->unidade);
                $setores = Pessoa::setoresSiglas($pessoa['codpes'],$this->unidade);
                $grupos = array_merge($setores,$vinculos) ;
                LdapUser::createOrUpdate($pessoa['codpes'], [
                    'nome' => $pessoa['nompes'],
                    'email' => $pessoa['codema']
                ],
                $grupos);
            }
        }
    }
}
