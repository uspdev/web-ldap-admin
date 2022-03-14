<?php

namespace App\Listeners;

use App\Ldap\User as LdapUser;
use App\Models\Config;
use Illuminate\Auth\Events\Login;
use Session;
use Uspdev\Replicado\Pessoa;

class LoginListener
{

    public function __construct()
    {
    }

    public function handle(Login $event)
    {
        // Pessoas que podem logar sem vínculo com a unidade
        $configs = Config::latest()->first();
        if ($configs) {
            $codpes_sem_vinculo = explode(',', $configs->codpes_sem_vinculo);
            $codpes_sem_vinculo = array_unique($codpes_sem_vinculo);
        } else {
            $codpes_sem_vinculo = [];
        }

        /**
         * Manter retrocompatibilidade, pois esse sistema chama o codpes de username
         * 25/06/2021: atualização do senhaunica-socialite para 3.x
         **/
        $event->user->username = $event->user->codpes;
        $event->user->save();

        $vinculos = Pessoa::vinculosSetores($event->user->username, config('web-ldap-admin.replicado_unidade'));

        // Como usamos a função array_merge, as respostas nulas devem ser arrays vazios
        if ($vinculos == null) {

            // Pessoa sem vinculo e não autorizado vai ser deslogado
            if (!in_array($event->user->username, $codpes_sem_vinculo)) {
                Session::flash('alert-danger', 'Pessoa sem vínculo com essa unidade');
                auth()->logout();
                return redirect('/');
            }

            // Pessoa não tem vínculo, mas pode logar
            $pessoa = [
                'codpes' => $event->user->codpes,
                'nompesttd' => $event->user->name,
                'codema' => $event->user->email,
                'tipvinext' => 'Externo',
                'dtanas' => '', # força senha inicial random
                'nomabvset' => '', # não traz o setor por ser Externo
            ];
        }

        // TODO completar os valores necessários quando a pessoa não tem vínculo, mas pode logar
        if (config('web-ldap-admin.sincLdapLogin') == 1) {
            if (!isset($pessoa)) {
                // Com vínculo ativo ('ALUNOGR', 'ALUNOPOS', 'ALUNOCEU', 'ALUNOEAD', 'ALUNOPD', 'ALUNOCONVENIOINT', 'SERVIDOR', 'ESTAGIARIORH')
                // TODO precisa melhorar a criação do array pessoa para chamar o método para criar ou atualizar
                // Principalmente se a pessoa for Servidor e também Alunode Graduação, Aluno de Pós-Graduação ou outro vínculo dos mencionados acima
                $tiposVinculos = Pessoa::tiposVinculos(config('web-ldap-admin.replicado_unidade'));
                foreach ($vinculos as $vinculo) {
                    if (array_search($vinculo, array_column($tiposVinculos, 'tipvinext'))) {
                        $vinculoPessoa = $vinculo;
                    }
                }
                $pessoas = Pessoa::ativosVinculo($vinculoPessoa, config('web-ldap-admin.replicado_unidade'));
                $pessoa = array_search($event->user->username, array_column($pessoas, 'codpes'));
                $pessoa = $pessoas[$pessoa];
            }

            // Chama método para criar ou atualizar passando o array da pessoa
            LdapUser::criarOuAtulizarPorArray($pessoa);

            Session::flash('alert-success', 'Informações sincronizadas com Sistemas Corporativos');
        }
    }
}
