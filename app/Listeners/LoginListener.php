<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Session;
use Uspdev\Replicado\Pessoa;
use App\Ldap\User as LdapUser;
use App\Models\Config;

class LoginListener
{

    public function __construct(){
    }

    public function handle(Login $event){

        $configs = Config::latest()->first();
        $codpes_sem_vinculo = explode(',',$configs->codpes_sem_vinculo);
        $codpes_sem_vinculo = array_unique($codpes_sem_vinculo);

        /**
         * Manter retrocompatibilidade, pois esse sistema chamado o codpes de username
         * 25/06/2021: atualização do senhaunica-socialite para 3.x
         **/
        $event->user->username = $event->user->codpes;
        $event->user->save();

        $vinculos = Pessoa::obterSiglasVinculosAtivos($event->user->codpes);
        if($vinculos == null) $vinculos=[];

        if(empty($vinculos) & !in_array($event->user->username,$codpes_sem_vinculo)){
            Session::flash('alert-danger', 'Pessoa sem vínculo com essa unidade');
            auth()->logout();
        }

        if (config('web-ldap-admin.sincLdapLogin') == 1) {
        
            $attr = [
                'nome'  => $event->user->name,
                'email' => $event->user->email,
                'setor' => ''
            ];
    
            $setores = Pessoa::obterSiglasSetoresAtivos($event->user->codpes);
            if($setores == null) $setores = []; 

            if(!empty($setores)){
                $attr['setor'] = $setores[0]; # Não é a melhor escolha
            }
            $password = date('dmY', strtotime(Pessoa::dump($event->user->codpes, ['dtanas'])['dtanas']));

            $groups = array_merge($vinculos, $setores);
    
            LdapUser::createOrUpdate($event->user->codpes,$attr,$groups,$password);
            Session::flash('alert-success', 'Informações sincronizadas com Sistemas Corporativos');
        }
    }


}