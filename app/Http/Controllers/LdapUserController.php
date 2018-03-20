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

        if (!is_null($logado)) {
            $user = Adldap::search()->users()->find($logado->id);
            if(!is_null($user)){

                // Alguns atributos    
                $attr = [];

                $attr['display_name'] = $user->getDisplayName();

                $attr['email'] = $user->getEmail();

                $attr['senha_alterada_em'] = $user->getPasswordLastSetDate();

                $attr['grupos'] = $user->getGroupNames();
            
                $attr['quota'] = round($user->quota[0]/1024,2);
            
                $attr['expira'] = $user->expirationDate();

                $attr['drive'] = $user->getHomeDrive();

                $attr['dir'] = $user->getHomeDirectory();
           
                $attr['ativacao'] = Carbon::createFromFormat('YmdHis\.0\Z', $user->whencreated[0])->toDateTimeString();

                return view('ldapusers.show',compact('attr'));
     
            }
        }
    }

    public function mudaSenha(Request $resquest){
    
    }
}
