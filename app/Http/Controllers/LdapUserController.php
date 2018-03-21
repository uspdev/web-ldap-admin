<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Adldap\Laravel\Facades\Adldap;
use Carbon\Carbon;

class LdapUserController extends Controller
{
    public function __construct() {
       $this->middleware('auth');
    }

    public function show(){
        $logado = \Auth::user();
        $attr = [];

        if (!is_null($logado)) {
            $user = Adldap::search()->users()->find($logado->id);
            if(!is_null($user)){

                // atualiza alguns atríbutos
                $name = trim($logado->name);
                $name_array = explode(' ',$name);
                $firstName = array_shift($name_array);
                $lastName = implode(' ',$name_array);

                $user->setDisplayName($name);
                $user->setFirstName($firstName);
                $user->setLastName($lastName);

                $user->setHomeDrive(env('LDAP_HOMEDRIVE') . ':');
                $user->setHomeDirectory('\\\\'. env('LDAP_SERVERFILE'). '\\' . $logado->id);
                $user->setEmail($logado->email);
                $user->save();

                // retorna alguns atributos    
                $attr['display_name'] = $user->getDisplayName();

                $attr['email'] = $user->getEmail();

                $last = $user->getPasswordLastSetDate();
                if(!is_null($last)) { 
                    $last = Carbon::createFromFormat('Y-m-d H:i:s', $last)->format('d/m/Y');
                }
                $attr['senha_alterada_em'] = $last;

                $attr['grupos'] = $user->getGroupNames();
            
                $attr['quota'] = round($user->quota[0]/1024,2);
            
                $expira = $user->expirationDate();
                if(!is_null($expira)) {
                    $expira = Carbon::instance($expira)->format('d/m/Y');
                }
                $attr['expira'] = $expira;

                $attr['drive'] = $user->getHomeDrive();

                $attr['dir'] = $user->getHomeDirectory();
           
                $ativacao = $user->whencreated[0];
                if(!is_null($ativacao)) {
                    $ativacao = Carbon::createFromFormat('YmdHis\.0\Z', $ativacao)->format('d/m/Y');
                }
                $attr['ativacao'] = $ativacao;
            }
            else {
                // Será substituído pela opção "ativar conta", que criará o usuário no ldap
                $attr['msg'] = "Conta não ativada. Envie um e-mail para suporteadm@usp.br com seu número USP para ativar sua conta no domínio da fflch";
            } 
        }

        return view('ldapusers.show',compact('attr'));
    }

    public function mudaSenha(Request $request){

        $request->validate([
           'senha' => 'required|min:8',
        ]);
 
        if($request->senha != $request->repetir_senha){
            $request->session()->flash('alert-danger', 'As senhas digitadas não são iguais, senha não alterada!');
            return redirect('/ldapusers');          
        }
        
        $logado = \Auth::user();

        if (!is_null($logado)) {
            $user = Adldap::search()->users()->find($logado->id);
            if(!is_null($user)){
                $user->setPassword($request->senha);
                $user->save();
                $request->session()->flash('alert-success', 'Senha alterada com sucesso!');
                return redirect('/ldapusers');          
            }
        }
    }
}
