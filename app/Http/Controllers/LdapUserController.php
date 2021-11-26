<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Ldap\User as LdapUser;
use App\Ldap\Group as LdapGroup;
use Carbon\Carbon;
use Adldap\Laravel\Facades\Adldap;

use App\Policies\LdapUserPolicy;

use App\Jobs\SincronizaReplicado;

use Uspdev\Replicado\Pessoa;
use Uspdev\Replicado\Graduacao;
use Uspdev\Replicado\Posgraduacao;
use App\Rules\LdapEmailRule;
use App\Rules\LdapUsernameRule;
use Illuminate\Database\Eloquent\Collection;

use Auth;

class LdapUserController extends Controller
{
    public function __construct() {
       $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('admin');

        // Registros por página
        if (empty($request->perPage)) {
            $perPage = config('web-ldap-admin.registrosPorPagina');
        } else {
            $perPage = $request->perPage;
        }

        // Verifica qual a página
        if (empty($request->page)) {
            $page = 1;
        } else {
            $page = $request->page;
        }

        // Busca
        $ldapusers = Adldap::search()->users();

        if(!empty($request->search) && isset($request->search)){
            // buscar por username ou por nome
            $check = clone $ldapusers;
            if(count($check->where('samaccountname','contains',$request->search)->get()) > 0) {
                $ldapusers = $ldapusers->where('samaccountname','contains',$request->search);
            } else {
                $ldapusers = $ldapusers->where('displayname','contains',$request->search);
            }
        }

        if(!empty($request->grupos) && isset($request->grupos)){
            if (count($request->grupos) > 1) {
                for ($i = 0; $i < count($request->grupos); $i++) {
                    $group = Adldap::search()->groups()->find($request->grupos[$i]);
                    $ldapusers = $ldapusers->orWhere('memberof', '=', $group->getDnBuilder()->get());
                }
            } else {
                $group = Adldap::search()->groups()->find($request->grupos[0]);
                $ldapusers = $ldapusers->where('memberof','=',$group->getDnBuilder()->get());
            }
        }

        // remove usuários default do sistema
        $ldapusers = $ldapusers->where('samaccountname','!=','Administrator');
        $ldapusers = $ldapusers->where('samaccountname','!=','krbtgt');
        $ldapusers = $ldapusers->where('samaccountname','!=','Guest');

        // Ordenando
        $ldapusers = $ldapusers->sortBy('displayname', 'asc');

        // Paginando
        $ldapusers = $ldapusers->paginate($perPage, $page);

        $grupos = LdapGroup::listaGrupos();

        return view('ldapusers.index', compact('ldapusers', 'grupos', 'request'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->authorize('admin');
        return view('ldapusers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('admin');

        // Validações
        $request->validate([
            'nome'      => ['required'],
            'email'     => ['required','email', new LdapEmailRule],
            'username'  => ['required','regex:/^[a-zA-Z0-9]*$/i', new LdapUsernameRule],
        ]);

        LdapUser::createOrUpdate($request->username, [
            'nome'  => $request->nome,
            'email' => $request->email,
            'setor' => 'NAOREPLICADO'
        ],
        ['NAOREPLICADO']);

        $request->session()->flash('alert-success', 'Usuário cadastrado com sucesso!');
        return redirect(config('app.url')."/ldapusers/{$request->username}");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $username)
    {
        $this->authorize('admin');
        $attr = LdapUser::show($username);
        #dd($attr);
        if( $attr ) {
            return view('ldapusers.show',compact('attr'));
        }
        $request->session()->flash('alert-danger', 'Essa conta não existe no ldap.');
        return redirect('/');
    }

    public function my(Request $request)
    {
        // Depois de tratado o id, vamos ver se a pessoa em questão tem acesso a essa página
        //$this->authorize('ldapusers.view',$username);
        $this->authorize('logado');

        $username = Auth::user()->username;
        $attr = LdapUser::show($username);
        if( $attr ) {
            return view('ldapusers.show',compact('attr'));
        }
        $request->session()->flash('alert-danger', 'Sua conta não existe no ldap. ');
        return redirect('/');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $username)
    {
        //$this->authorize('ldapusers.update', $id);
        $this->authorize('logado');

        // troca de senha
        if(!is_null($request->senha)) {
            $request->validate([
               'senha' => ['required','confirmed','min:8'],
            ]);

            LdapUser::changePassword($username,$request->senha);
            $request->session()->flash('alert-success', 'Senha alterada com sucesso!');
            return redirect('/');
        }

        // status
        if(!is_null($request->status)) {

            if($request->status == 'disable') {
                LdapUser::disable($username);
                $request->session()->flash('alert-success', 'Usuário Desabilitado');
                return redirect('/ldapusers/');
            }

            if($request->status == 'enable') {
                LdapUser::enable($username);
                $request->session()->flash('alert-success', 'Usuário Habilitado');
                return redirect('/ldapusers/');
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $username)
    {
        $this->authorize('admin');

        $attr = LdapUser::delete($username);

        $request->session()->flash('alert-danger', 'Usuário(a) '. $username .' deletado');
        return redirect('/ldapusers');
    }

    public function syncReplicadoForm(Request $request)
    {
        $this->authorize('admin');
        return view('ldapusers.sync');
    }

    public function syncReplicado(Request $request)
    {
        $this->authorize('admin');
        $this->validate($request, [
            'type' => 'required',
        ]);

        SincronizaReplicado::dispatch($request->type);
        $request->session()->flash('alert-success', 'Sincronização em andamento');
        return redirect('/ldapusers');
    }

}
