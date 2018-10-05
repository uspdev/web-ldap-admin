<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ldap\User as LdapUser;

use Carbon\Carbon;

class LdapUserController extends Controller
{
    public function __construct() {
       $this->middleware('auth');
    }

    public function show(){
        $logado = \Auth::user();

        if (!is_null($logado)) {
            LdapUser::createOrUpdate($logado->id);
        }
        dd('check ldap');
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
            LdapUser::changePassword($logado->id,$request->senha);
            $request->session()->flash('alert-success', 'Senha alterada com sucesso!');
            return redirect('/ldapusers');          
        }
    }
}
