<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Session;
use Uspdev\Replicado\Pessoa;
use App\Ldap\User as LdapUser;

class LoginListener
{

    public function __construct(){
    }

    public function handle(Login $event){

        /**
         * Manter retrocompatibilidade, pois esse sistema chamado o codpes de username
         * 25/06/2021: atualização do senhaunica-socialite para 3.x
         **/
        $event->user->username = $event->user->codpes;
        $event->user->save();

        $vinculos = Pessoa::obterSiglasVinculosAtivos($event->user->codpes);

        if(empty($vinculos)){
            Session::flash('alert-danger', 'Pessoa sem vínculo com essa unidade');
            auth()->logout();
        }

        $attr = [
            'nome'  => $event->user->name,
            'email' => $event->user->email,
            'setor' => ''
        ];

        $setores = Pessoa::obterSiglasSetoresAtivos($event->user->codpes);
        if($setores){
            $attr['setor'] = $setores[0]; # Não é a melhor escolha
        }
        $password = date('dmY', strtotime(Pessoa::dump($event->user->codpes, ['dtanas'])['dtanas']));
        $groups = array_merge($vinculos, $setores);
        LdapUser::createOrUpdate($event->user->codpes,$attr,$groups,$password);
        Session::flash('alert-success', 'Informações sincronizadas com Sistemas Corporativos');
    }


}