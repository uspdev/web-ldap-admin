<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\Solicita;
use App\Ldap\Group as LdapGroup;
use Adldap\Laravel\Facades\Adldap;

class SolicitaController extends Controller
{
    /**
     * Solicitação de conta de administração local do windows
     */
    public function create(Request $request)
    {
        $this->authorize('logado');
        $user = Auth::user();
        /*
        $ldap_computers = Adldap::search()->computers()->get();
        $computers = Collection::make([]);
        foreach($ldap_computers as $computer){
            $hostname = $computer->getDnsHostName();
            $basedn = config('adldap.base_dn');
            //dd($basedn);
            //$basedn = str_replace('DC=','',config('adldap.base_dn'));
            //$basedn = str_replace(',','\.',));
            $hostname = str_replace(".$basedn", "", $hostname);
            $computers->push(['hostname' => $hostname]);
        }
        */

        $computers = ['pc1','pc2'];

        return view('solicita.create',[
            'computers' => $computers
        ]);
    }

    public function store(Request $request){

        $this->authorize('logado');
        
        $request->validate([
            'computer'      => ['required'],
            'justificativa' => ['required'],
            'ciencia1'      => ['required'],
            'ciencia2'      => ['required'],
            'ciencia3'      => ['required'],
        ]);

        $solicita = new Solicita;
        $solicita->expired = false;
        $solicita->justificativa = $request->justificativa;
        $solicita->computer = $request->computer;
        $solicita->user_id = auth()->user()->id;
        $solicita->save();

        $ldapuser = Adldap::search()->users()->find(auth()->user()->username);

        $groupname = config('web-ldap-admin.localAdminGroupLdap');

        $group = LdapGroup::createOrUpdate($groupname);
        $group->addMember($ldapuser);
        $group->save();

        request()->session()->flash('alert-info',
            'Privilégio administrativo concedido no equipamento: ' . $request->computer .
            '.  Reinicie o computador para que os privilégios possam ser carregados. 
            Lembre-se que as permissões duram apenas 1 hora.');
        
        return redirect('/');
    }
}
