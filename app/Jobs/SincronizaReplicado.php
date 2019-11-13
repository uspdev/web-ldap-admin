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
            foreach (Pessoa::tiposVinculos($this->unidade) as $vinculo) {
                if ($type == $vinculo['tipvinext']) {
                    $this->sync(Pessoa::ativosVinculo($vinculo['tipvinext'], $this->unidade));
                }   
            }             
        }
    }

    public function sync($pessoas)
    {
        if ($pessoas) {
            foreach($pessoas as $pessoa) {
                $vinculos = Pessoa::vinculosSiglas($pessoa['codpes'],$this->unidade);
                $setores = Pessoa::setoresSiglas($pessoa['codpes'],$this->unidade);
                // $grupos = array_merge($setores,$vinculos);
                $vinculosRegulares = ['ALUNOGR', 'ALUNOPOS', 'ALUNOCEU', 'ALUNOEAD', 'ALUNOPD', 'SERVIDOR', 'ESTAGIARIORH'];
                $gruposModificados = [];
                $setoresModificados = [];
                foreach ($vinculosRegulares as $vinculoRegular) {
                    foreach ($vinculos as $vinculo) {
                        $grupoModificado = str_replace('-' . $this->unidade, '', $vinculo);
                        if ($vinculoRegular == $vinculo) {
                            array_push($gruposModificados, $grupoModificado);
                        }                        
                    }
                }
                foreach ($setores as $setor) {
                    $setorModificado = str_replace('-' . $this->unidade, '', $setor);
                    array_push($setoresModificados, $setorModificado);
                }
                $grupos = array_merge($gruposModificados, $setoresModificados);
                LdapUser::createOrUpdate($pessoa['codpes'], [
                    'nome' => $pessoa['nompesttd'],
                    'email' => $pessoa['codema'],
                    'setor' => str_replace('-' . $this->unidade, '', $pessoa['nomabvset'])
                ],
                $grupos);
            }
        }
    }
}
