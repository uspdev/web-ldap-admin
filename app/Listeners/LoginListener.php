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

        $vinculos = Pessoa::obterSiglasVinculosAtivos($event->user->codpes);

        // Como usamos a função array_merge, as respostas nulas devem ser arrays vazios
        if ($vinculos == null) {
            $vinculos = [];
        }

        if (empty($vinculos) & !in_array($event->user->username, $codpes_sem_vinculo)) {
            Session::flash('alert-danger', 'Pessoa sem vínculo com essa unidade');
            auth()->logout();
            return redirect('/');
        }

        if (config('web-ldap-admin.sincLdapLogin') == 1) {

            // criarOuAtulizarPorCodpes($event->user->codpes, $event->user)

            // vamos criar ou atualizar a conta automaticamente no login
            $attr = [
                //'displayname', 'mail', 'telephonenumber', 'givenname', 'description', 'department', 'sn - surname',
                'nome' => $event->user->name,
                'email' => $event->user->email,
                'setor' => '',
            ];

            $setores = Pessoa::obterSiglasSetoresAtivos($event->user->codpes);
            if (!empty($setores)) {
                $attr['setor'] = $setores[0]; # Não é a melhor escolha
            }
            //Não vamos setar password no login pois, ou o usuário já tem, ou vai ter de mudar pela própria interface
            //$password = date('dmY', strtotime(Pessoa::dump($event->user->codpes, ['dtanas'])['dtanas']));

            // Como usamos a função array_merge, as respostas nulas devem ser arrays vazios
            if ($setores == null) {
                $setores = [];
            }

            // colocar um "externo" se não tiver vinculo na unidade.

            // Vincula o grupo aos setores correspondentes
            $groups = array_merge($vinculos, $setores);

            // setando username e codpes
            switch (strtolower(config('web-ldap-admin.campoCodpes'))) {
                case 'telephonenumber':
                    $username = explode('@', $event->user->email)[0];
                    $username = preg_replace("/[^a-zA-Z0-9]+/", "", $username); //email sem caracteres especiais
                    $attr['telephonenumber'] = $event->user->codpes;
                    break;
                case 'username':
                default:
                    $username = $event->user->codpes;
                    $attr['telephonenumber'] = '';
                    break;
            }

            LdapUser::createOrUpdate($username, $attr, $groups);
            Session::flash('alert-success', 'Informações sincronizadas com Sistemas Corporativos');
        }
    }

}
