<?php
namespace App\Http\Controllers;

use Adldap\Laravel\Facades\Adldap;
use App\Jobs\SincronizaReplicado;
use App\Ldap\Group as LdapGroup;
use App\Ldap\User as LdapUser;
use App\Rules\LdapEmailRule;
use App\Rules\LdapUsernameRule;
use Auth;
use Illuminate\Http\Request;
use Uspdev\Replicado\Posgraduacao;

class LdapUserController extends Controller
{
    public function __construct()
    {
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
        \UspTheme::activeUrl('ldapusers');

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

        if (!empty($request->search) && isset($request->search)) {
            // buscar por username ou por nome
            $check = clone $ldapusers;
            if (count($check->where('samaccountname', 'contains', $request->search)->get()) > 0) {
                $ldapusers = $ldapusers->where('samaccountname', 'contains', $request->search);
            } else {
                $ldapusers = $ldapusers->where('displayname', 'contains', $request->search);
            }
        }

        if (!empty($request->grupos) && isset($request->grupos)) {
            if (count($request->grupos) > 1) {
                for ($i = 0; $i < count($request->grupos); $i++) {
                    $group = Adldap::search()->groups()->find($request->grupos[$i]);
                    $ldapusers = $ldapusers->orWhere('memberof', '=', $group->getDnBuilder()->get());
                }
            } else {
                $group = Adldap::search()->groups()->find($request->grupos[0]);
                $ldapusers = $ldapusers->where('memberof', '=', $group->getDnBuilder()->get());
            }
        }

        // oculta usuários default do sistema
        foreach (config('web-ldap-admin.ocultarUsuarios') as $usuario) {
            $ldapusers = $ldapusers->where('samaccountname', '!=', $usuario);
        }

        // Ordenando
        $ldapusers = $ldapusers->sortBy('displayname', 'asc');

        // Paginando
        $ldapusers = $ldapusers->paginate($perPage, $page);
        $pagCor = $ldapusers->getCurrentPage();
        $perPag = $ldapusers->getPerPage();
        $totPag = $ldapusers->getPages();
        // dd($ldapusers);
        $ldapusers = $ldapusers->getResults();

        $grupos = LdapGroup::listaGrupos();

        return view('ldapusers.index', compact('ldapusers', 'grupos', 'request', 'pagCor', 'perPag', 'totPag'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->authorize('admin');
        \UspTheme::activeUrl('ldapusers/create');

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
            'username' => ['required', 'regex:/^[a-zA-Z0-9]*$/i', new LdapUsernameRule],
            'nome' => ['required'],
            'email' => ['required', 'email', new LdapEmailRule],
            //'grupo' => ['']
        ]);

        LdapUser::createOrUpdate($request->username, [
            'nome' => $request->nome,
            'email' => $request->email,
            'setor' => 'NAOREPLICADO',
        ],
            [$request->grupo, 'NAOREPLICADO']);

        $request->session()->flash('alert-success', 'Usuário cadastrado com sucesso!');
        return redirect("ldapusers/{$request->username}");
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
        $user = LdapUser::obterUserPorUsername($username);
        if (!$user) {
            $request->session()->flash('alert-danger', "A conta $username não existe no ldap.");
            return view('ldapusers.show-no-user');
        }
        return SELF::showCommon($user);
    }

    public function my(Request $request)
    {
        $this->authorize('user');
        \UspTheme::activeUrl('ldapusers/my');

        $user = LdapUser::obterUserPorCodpes(Auth::user()->codpes);
        if (!$user) {
            $request->session()->flash('alert-danger', 'Sua conta não existe no ldap. ');
            return view('ldapusers.show-no-user');
        }
        return SELF::showCommon($user);
    }

    /**
     * parte comum do show para show e my
     */
    protected function showCommon($user)
    {
        $attr = LdapUser::show($user);

        $vinculos = [];
        if ($codpes = LdapUser::obterCodpes($user)) {
            $vinculos = [
                [
                    'tipvin' => 'ALUNOPOS',
                    'tipvinext' => 'Aluno de pós graduação',
                ],
            ];
            foreach ($vinculos as &$vinculo) {
                switch ($vinculo['tipvin']) {
                    case 'ALUNOPOS':
                        $pg = Posgraduacao::obterVinculoAtivo($codpes);
                        $vinculo = array_merge($vinculo, $pg);
                        break;
                }
            }
        }

        return view('ldapusers.show', compact('attr', 'user', 'vinculos'));
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
        $this->authorize('user');

        // troca de senha
        if (!is_null($request->senha)) {
            $request->validate([
                'senha' => ['required', 'confirmed', 'min:8'],
            ]);

            if (LdapUser::changePassword($username, $request->senha)) {
                $request->session()->flash('alert-success', 'Senha alterada com sucesso!');
            } else {
                $request->session()->flash('alert-danger',
                    'Não foi possível alterar a senha da sua conta! Consulte a política de senha de seu servidor.');
            }

            return redirect()->back();
        }

        // status
        if (!is_null($request->status)) {

            if ($request->status == 'disable') {
                LdapUser::disable($username);
                $request->session()->flash('alert-success', "Usuário $username desabilitado!");
                return redirect()->back();
            }

            if ($request->status == 'enable') {
                LdapUser::enable($username);
                $request->session()->flash('alert-success', "Usuário $username habilitado!");
                return redirect()->back();
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

        $request->session()->flash('alert-danger', 'Usuário(a) ' . $username . ' deletado');
        return redirect()->back();
    }

    public function syncReplicadoForm(Request $request)
    {
        $this->authorize('admin');

        $vinculos = \Uspdev\Replicado\Pessoa::tiposVinculos(config('web-ldap-admin.replicado_unidade'));
        foreach ($vinculos as &$vinculo) {
            $vinculo['countReplicado'] = \Uspdev\Replicado\Pessoa::ativosVinculo($vinculo['tipvinext'], config('web-ldap-admin.replicado_unidade'), 1)[0]['total'];
            $vinculo['countAD'] = count(\App\Ldap\User::getUsersGroup($vinculo['tipvinext']));
            $vinculo['style'] = $vinculo['countAD'] < $vinculo['countReplicado'] ? 'text-danger' : '';
        }
        return view('ldapusers.sync', compact('vinculos'));
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
