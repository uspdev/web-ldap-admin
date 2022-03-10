<?php

namespace App\Jobs;

use App\Ldap\User as LdapUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Uspdev\Replicado\Graduacao;
use Uspdev\Replicado\Pessoa;

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
        $this->unidade = env('REPLICADO_CODUNDCLG');
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->type as $type) {
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
                 * Perdeu vínculo com a unidade, remover dos grupos, adicionar
                 * ao grupo Desativados e desativar a conta.
                 * Para se verificar os desligados
                 * Comparar as contas do AD por grupo principal
                 * Servidor, Docente, Docente Aposentado, Estagiário,
                 * Aluno de Graduação, Aluno de Pós-Graduação, Aluno de Cultura e Extensão,
                 * Aluno Escola de Arte Dramática, Pós-doutorando, Aluno Convênio Interc Grad
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
            foreach ($pessoas as $pessoa) {
                $username = $pessoa['codpes'];
                //$password = date('dmY', strtotime(Pessoa::dump($pessoa['codpes'], ['dtanas'])['dtanas']));
                $password = 'TrocaXr123!()';
                
                $setor = str_replace('-' . $this->unidade, '', $pessoa['nomabvset']);
                if (empty($setor)) {
                    $setor = $pessoa['tipvinext'];
                    if ($pessoa['tipvinext'] == 'Aluno de Graduação') {
                        $nomabvset = Graduacao::setorAluno($pessoa['codpes'], $this->unidade)['nomabvset'];
                        $setor = $pessoa['tipvinext'] . ' ' . $nomabvset;
                    }
                } else {
                    $setor = $pessoa['tipvinext'] . ' ' . $setor;
                }

                $attr = [
                    'nome' => $pessoa['nompesttd'],
                    'email' => $pessoa['codema'],
                    'setor' => $setor,
                ];
                $grupos = Pessoa::vinculosSetores($pessoa['codpes'], $this->unidade);
                $grupos = array_unique($grupos);
                sort($grupos);
                LdapUser::createOrUpdate($username, $attr, $grupos, $password);
            }
        }
    }
}
