<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\Solicita;
use App\Ldap\Group as LdapGroup;
use App\Ldap\User as LdapUser;

use LdapRecord\Models\ActiveDirectory\Computer;
use Carbon\Carbon;

class SolicitaController extends Controller
{
    /**
     * Solicitação de conta de administração local do windows
     */
    public function create(Request $request)
    {
        // menu "Solicitação de Conta de Administrador"

        $this->authorize('user');

        // a url ativa não está funcionando com menu dinâmico issue #98
        \UspTheme::activeUrl('solicita');

        $user = Auth::user();
        $ldap_computers = Computer::orderBy('cn')->get();
        $computers = [];

        foreach ($ldap_computers as $computer) {

            $lastLogon = $computer->getFirstAttribute('lastlogontimestamp');
            $os = $computer->getFirstAttribute('operatingsystem');

            if ($lastLogon && $os) {
                $seconds = ((float)$lastLogon / 10000000) - 11644473600;
                $carbon = Carbon::createFromTimestamp($seconds);

                if ($carbon->diffInDays(Carbon::now()) < 120) {
                    array_push($computers, [
                        'computer' => $computer->getFirstAttribute('cn'),
                        'os' => $os,
                        'lastLogon' => $carbon->format('d/m/Y H:i:s')
                    ]);
                }
            }
        }
        return view('solicita.create',[
            'computers' => $computers
        ]);
    }

    public function store(Request $request){

        // menu "Solicitação de Conta de Administrador" -> botão "Enviar"

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

        $user = LdapUser::obterUserPorUsername(auth()->user()->username);

        $groupname = config('web-ldap-admin.localAdminGroupLdap');
        $group = LdapGroup::createOrUpdate($groupname);

        if(!$user->groups()->exists($group)){
            $group->members()->attach($user);
        }

        request()->session()->flash('alert-info',
            'Privilégio administrativo concedido no equipamento: ' . $request->computer .
            '.  Reinicie o computador para que os privilégios possam ser carregados.
            Lembre-se que as permissões duram apenas 1 hora.');

        return redirect('/');
    }
}
