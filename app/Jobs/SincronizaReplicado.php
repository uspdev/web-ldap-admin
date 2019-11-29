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
            
            // No .env foi configurado para desativar os desligados?
            if (config('web-ldap-admin.desativarDesligados') == true) {
                /**
                 * Perdeu vínculo com a unidade, remover dos grupos, adicionar ao grupo Desativados e destivar a conta. 
                 * Para se verificar os desligados
                 * Comparar as contas do AD por grupo principal
                 * Servidor, Docente, Estagiário,
                 * Aluno de Graduação, Aluno de Pós-Graduação, Aluno de Cultura e Extensão, 
                 * Aluno Escola de Arte Dramática, Pós-doutorando
                 */   
                // Grupo principal
                $grupoPrincipal = $pessoas[0]['tipvinext'];
                // Array das pessoas do replicado
                $replicadoUsers = [];
                foreach ($pessoas as $pessoa) {
                    array_push($replicadoUsers, $pessoa['codpes']);
                }
                // Array das contas no AD 
                $contasAD = [];
                $ldapusers = LdapUser::getUsersGroup($grupoPrincipal);
                foreach ($ldapusers as $ldapuser) {
                    array_push($contasAD, $ldapuser->getAccountName());
                }
                // Verifica se alguma conta no AD não existe no replicado e guarda no array de desligados
                $desligados = array_values(array_diff($contasAD, $replicadoUsers));
                // Estas contas devem ser desativadas
                LdapUser::desativarUsers($desligados);
            }

            foreach($pessoas as $pessoa) {
                $vinculos = Pessoa::vinculosSiglas($pessoa['codpes'],$this->unidade);
                $setores = Pessoa::setoresSiglas($pessoa['codpes'],$this->unidade);
                $vinculosRegulares = ['ALUNOGR', 'ALUNOPOS', 'ALUNOCEU', 'ALUNOEAD', 'ALUNOPD', 'SERVIDOR', 'ESTAGIARIORH'];
                $gruposModificados = [];
                $setoresModificados = [];
                foreach ($vinculosRegulares as $vinculoRegular) {
                    foreach ($vinculos as $vinculo) {
                        $grupoModificado = str_replace('-' . $this->unidade, '', $vinculo);                     
                    }
                }
                foreach ($setores as $setor) {
                    $setorModificado = str_replace('-' . $this->unidade, '', $setor);
                    array_push($setoresModificados, $setorModificado);
                }
                $grupos = array_merge($gruposModificados, $setoresModificados);
                $setor = str_replace('-' . $this->unidade, '', $pessoa['nomabvset']);
                if (empty($setor)) {
                    $setor = $pessoa['tipvin'];
                    $setor = $setor . ' ' . $pessoa['tipvinext'];
                    array_push($grupos, $setor);
                } else {
                    array_push($grupos, $setor);
                    $setor = $setor . ' ' . $pessoa['tipvinext'];
                    array_push($grupos, $setor);
                    array_push($grupos, $pessoa['tipvinext']);  
                }    
                $grupos = array_unique($grupos);
                sort($grupos);
                LdapUser::createOrUpdate($pessoa['codpes'], [
                    'nome' => $pessoa['nompesttd'],
                    'email' => $pessoa['codema'],
                    'setor' => $setor
                ],
                $grupos);
            }
        }
    }
}
