<?php

namespace App\Listeners;

use App\Ldap\User as LdapUser;
use App\Models\Config;
use Illuminate\Auth\Events\Login;
use Session;
use Uspdev\Replicado\Pessoa;
use Illuminate\Support\Arr;
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
            if (!in_array($event->user->codpes, $codpes_sem_vinculo)) {
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
                // Com vínculo ativo ('ALUNOGR', 'ALUNOPOS', 'ALUNOCEU', 'ALUNOEAD', 'ALUNOPD', 'ALUNOCONVENIOINT',
                // 'SERVIDOR', 'ESTAGIARIORH')
                // TODO precisa melhorar a criação do array pessoa para chamar o método para criar ou atualizar

                // Principalmente se a pessoa for Servidor e também Alunode Graduação, Aluno de Pós-Graduação ou outro vínculo dos mencionados acima


                // Não entendi porque aqui estava sendo carregado todas pessoas do mesmo grupo
                // por exemplo: quando um aluno de graduação tenta logar, esse array é carregado com
                // milhares de alunos dependendo da unidade, tornando o login muito lento

                // Tem um erro também com a variável $vinculoPessoa que é criado dentro de um if
                // mas é chamada fora, e o sistema quebra quando o fluxo não entra no if
                // Vou deixar comentado até conversar com masaki e alessandro para otimizarmos esse login
                // 12/04/2022 - @thiagogomesverissimo

                /*
                $tiposVinculos = Pessoa::tiposVinculos(config('web-ldap-admin.replicado_unidade'));
                foreach ($vinculos as $vinculo) {
                    if (array_search($vinculo, array_column($tiposVinculos, 'tipvinext'))) {
                        $vinculoPessoa = $vinculo;
                    }
                }
                $pessoas = Pessoa::ativosVinculo($vinculoPessoa, config('web-ldap-admin.replicado_unidade'));
                $pessoa = array_search($event->user->username, array_column($pessoas, 'codpes'));
                $pessoa = $pessoas[$pessoa];
                */

                // $pessoa = Pessoa::dump($event->user->codpes);
                // $pessoa['codema'] = $event->user->email;

                // por ora vou deixar esses campos vazios, pois entendi que eles
                // deveria ser preenchidos a partir do LOCALIZAPESSOA
                // mas não temos um critério ainda de como eleger o melhor vínculo
                // 12/04/2022 - @thiagogomesverissimo
                // $pessoa['tipvinext'] = '';
                // $pessoa['nomabvset'] = '';

                // Principalmente se a pessoa for Servidor e também Alunode Graduação, Aluno de Pós-Graduação 
                // ou outro vínculo dos mencionados acima

                // se a pessoa tiver mais de un vinculo precisa escolher 1.
                // $tiposVinculos = Pessoa::tiposVinculos(config('web-ldap-admin.replicado_unidade'));
                // $tiposVinculos = Arr::pluck($tiposVinculos, 'tipvinext');
                // // dd($tiposVinculos);
                // foreach ($vinculos as $vinculo) {
                //     if (array_search($vinculo, array_column($tiposVinculos, 'tipvinext'))) {
                //         $vinculoPessoa = $vinculo;
                //     }
                // }
                // $pessoas = Pessoa::ativosVinculo($vinculoPessoa, config('web-ldap-admin.replicado_unidade'));
                // $pessoa = array_search($event->user->username, array_column($pessoas, 'codpes'));
                // $pessoa = $pessoas[$pessoa];

                $pessoa = Pessoa::listarVinculosAtivos($event->user->codpes)[0];
                // $pessoa = Pessoa::dump($event->user->codpes);
            }

            // Chama método para criar ou atualizar passando o array da pessoa
            
            LdapUser::criarOuAtualizarPorArray($pessoa);

            Session::flash('alert-success', 'Informações sincronizadas com Sistemas Corporativos');
        }
    }
}
