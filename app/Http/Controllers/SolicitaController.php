<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\Solicita;
use App\Ldap\Group as LdapGroup;
use Adldap\Laravel\Facades\Adldap;
use Carbon\Carbon;

class SolicitaController extends Controller
{
    /**
     * Solicitação de conta de administração local do windows
     */
    public function create(Request $request)
    {
        $this->authorize('user');
        $user = Auth::user();
        $ldap_computers = Adldap::search()->computers()->sortBy('cn')->get();
        $computers = [];

        foreach($ldap_computers as $computer){
            // Mostrar apenas as máquinas com login nos últimos 120 dias
            $carbon = Carbon::createFromTimestamp($computer->getLastLogonTimestamp()/10000000  - 11644473600);
            if(!is_null($computer->getOperatingSystem()) & $carbon->diffInDays(Carbon::now()) < 120 ) {
            array_push($computers,[
                'computer' => $computer->getName(),
                'os'       => $computer->getOperatingSystem(),
                'lastLogon'       => $carbon->format('d/m/Y H:i:s')
                ]);
            }
        }
        return view('solicita.create',[
            'computers' => $computers
        ]);
    }

    public function store(Request $request){

        $this->authorize('user');

        $request->validate([
            'computer'      => ['required'],
            'justificativa' => ['required'],
            'ciencia1'      => ['required'],
            'ciencia2'      => ['required'],
            'ciencia3'      => ['required'],
        ]);

        if(Solicita::where('user_id', auth()->user()->id)->where('expired',false)->get()->isNotEmpty()){
            request()->session()->flash('alert-danger',
            'Atenção: Você já está com uma solicitação em aberto, reinicie seu computador para
            obter os privilégios.');
            return redirect('/');
        }

        $solicita = new Solicita;
        $solicita->expired = false;
        $solicita->justificativa = $request->justificativa;
        $solicita->computer = $request->computer;
        $solicita->user_id = auth()->user()->id;
        $solicita->save();

        $ldapuser = Adldap::search()->users()->where('cn', '=', auth()->user()->username)->first();

        $groupname = config('web-ldap-admin.localAdminGroupLdap');
        $group = LdapGroup::createOrUpdate($groupname);

        if(!$ldapuser->inGroup($groupname)){
            $group->addMember($ldapuser);
            $group->save();
        }

        request()->session()->flash('alert-info',
            'Privilégio administrativo concedido no equipamento: ' . $request->computer .
            '.  Reinicie o computador para que os privilégios possam ser carregados.
            Lembre-se que as permissões duram apenas 1 hora.');

        return redirect('/');
    }
}
