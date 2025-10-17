<?php
namespace App\Http\Controllers;

use Adldap\Laravel\Facades\Adldap;
use App\Jobs\SincronizaReplicado;
use App\Ldap\Group as LdapGroup;
use App\Ldap\User as LdapUser;
use App\Replicado\Replicado;
use App\Rules\LdapEmailRule;
use App\Rules\LdapUsernameRule;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Uspdev\Replicado\Pessoa;
use Uspdev\Utils\Generic as Utils;

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
        $this->authorize('gerente');
        \UspTheme::activeUrl('ldapusers');

        //vamos validar os campos??
        $request->validate([
            'perPage' => 'nullable',
            'page' => 'nullable',
            'search' => 'nullable',
            'grupos' => 'nullable',
        ]);

        // Registros por página
        $perPage = empty($request->perPage) ? config('web-ldap-admin.registrosPorPagina') : $request->perPage;

        // Verifica qual a página
        $page = empty($request->page) ? 1 : $request->page;

        // Busca
        $ldapusers = Adldap::search()->users();

        if (!empty($request->search) && isset($request->search)) {
            // buscar por username ou por nome
            $ldapusers = $ldapusers->orWhere('displayname', 'contains', $request->search)
                ->orWhere('samaccountname', 'contains', $request->search);
        }

        if (!empty($request->grupos) && isset($request->grupos)) {
            if (count($request->grupos) > 1) {
                for ($i = 0; $i < count($request->grupos); $i++) {
                    $group = Adldap::search()->groups()->find($request->grupos[$i]);
                    $ldapusers = $ldapusers->orWhere('memberof', '=', $group->getDnBuilder()->get());
                }
            } else {
                //TODO: aqui nao acho que precise separado do FOR
                $group = Adldap::search()->groups()->find($request->grupos[0]);
                $ldapusers = $ldapusers->where('memberof', '=', $group->getDnBuilder()->get());
            }
        }

        // oculta usuários default do sistema
        foreach (config('web-ldap-admin.ocultarUsuarios') as $usuario) {
            $ldapusers = $ldapusers->where('samaccountname', '!=', $usuario);
        }

        // mostra somente os usuários habilitados
        // $ldapusers = $ldapusers->whereEnabled();

        // tentativa de mostrar somente contas não expiradas
        // $ldapusers = $ldapusers->whereBetween('accountexpires', [(new \DateTime('October 1st 2010'))->format('YmdHis.0\Z'), now()->format('YmdHis.0\Z')]);

        // Ordenando
        $ldapusers = $ldapusers->sortBy('displayname', 'asc');

        // Paginando
        // pagina começa no 0 mas vamos mostrar começando no 1
        $ldapusers = $ldapusers->paginate($perPage, $page - 1);
        $nav['pagCor'] = $ldapusers->getCurrentPage() + 1;
        $nav['perPag'] = $ldapusers->getPerPage();
        $nav['totPag'] = $ldapusers->getPages();
        $nav['offset'] = $ldapusers->getCurrentOffSet();

        $maxLnk = 5; # Máximo de links
        $lnkLat = ceil($maxLnk / 2); # Cálculo dos links laterais
        $nav['pagIni'] = $nav['pagCor'] - $lnkLat; # Início dos links (esquerda)
        $nav['pagFin'] = $nav['pagCor'] + $lnkLat; # Fim dos links (direita)

        $searchGrupos = '';
        $gruposUrl = '';
        if (!empty($request->grupos) && isset($request->grupos)) {
            $searchGrupos = implode(', ', $request->grupos);
            foreach ($request->grupos as $grupo) {
                $gruposUrl .= "&grupos[]=$grupo";
            }
        }

        $grupos = LdapGroup::listaGrupos();

        return view('ldapusers.index', compact('ldapusers', 'grupos', 'request', 'nav', 'gruposUrl', 'searchGrupos', 'perPage'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->authorize('gerente');
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
        $this->authorize('gerente');

        if (isset($request->acao) && $request->acao == 'criar-por-codpes') {
            $request->validate([
                'codpes' => 'required|integer',
            ]);

            // verifincado se usuário já existe no ldap
            if ($user = LdapUser::obterUserPorCodpes($request->codpes)) {
                $request->session()->flash('alert-info', 'Usuário já existe!');
                return redirect("ldapusers/" . $user->GetaccountName());
            }

            // verificando se o codpes possui vinculo ativo na unidade
            $pessoa = Pessoa::listarVinculosAtivos($request->codpes);
            if (!$pessoa) {
                $request->session()->flash('alert-warning', 'Número USP ' . $request->codpes . ' não encontrado!');
                return back();
            }
            // dd($pessoa);

            // criando usuario a partir do codpes
            $user = LdapUser::criarOuAtualizarPorArray($pessoa[0]);
            $user->setPassword($password = Utils::senhaAleatoria());
            $user->save();

            $request->session()->flash('alert-success', 'Usuário cadastrado com sucesso!');
            return redirect("ldapusers/" . $user->GetaccountName())->with('password', $password);
        }

        // criando usuário com dados manuais
        $request->validate([
            'username' => ['required', 'regex:/^[a-zA-Z0-9]*$/i', new LdapUsernameRule],
            'nome' => ['required'],
            'email' => ['required', 'email', new LdapEmailRule],
        ]);

        $grupos = array_merge($request->grupos, ['NAOREPLICADO']);
        $attr = [
            'nome' => $request->nome,
            'email' => $request->email,
            'setor' => 'NAOREPLICADO',
        ];
        $user = LdapUser::createOrUpdate($request->username, $attr, $grupos);

        $request->session()->flash('alert-success', 'Usuário cadastrado com sucesso!');
        return redirect("ldapusers/" . $user->GetaccountName());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $username)
    {
        $this->authorize('gerente');
        \UspTheme::activeUrl('ldapusers');

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
     * parte comum do para show e my
     */
    protected function showCommon($user)
    {
        $attr = LdapUser::show($user);
        $vinculos = [];
        // o $codpesValido serve para informar se o codpes extraído veio do campo indicado no config
        list($codpes, $codpesValido) = LdapUser::obterCodpes($user, true);

        if ($codpes) {
            $vinculos = Replicado::listarVinculosEstendidos($codpes);
            $foto = \Uspdev\Wsfoto::obter($codpes);
        } else {
            $foto = '';
        }

        return view('ldapusers.show', compact('attr', 'user', 'vinculos', 'codpesValido', 'foto'));
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
                // TODO: 04/07/2022 - ECAdev @alecosta: Parametrizar
                // 'senha' => ['required', 'confirmed', 'min:8'], # Sem complexidade
                'senha' => ['required', 'confirmed', 'min:8|max:20', 'regex:/[0-9]/', 'regex:/[A-Z]/', 'regex:/[!@#\$%\^&\*()_]/'], # Com complexidade
                'must_change_pwd' => ['nullable', 'in:1'],
            ]);

            if (LdapUser::changePassword($username, $request->senha, $request->must_change_pwd)) {
                $request->session()->flash('alert-success', 'Senha alterada com sucesso!');
            } else {
                $request->session()->flash('alert-danger',
                    'Não foi possível alterar a senha da sua conta! Consulte a política de senha de seu servidor.');
            }

            return redirect()->back();
        }

        $this->authorize('gerente');
        // atualiza status
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

        // atualiza data de expiração
        if (!is_null($request->expiry)) {
            $request->validate([
                'expiry' => ['required', Rule::in([7, 30, 365, 0, -1])],
            ]);

            LdapUser::expirarSenha($username, $request->expiry);
            $request->session()->flash('alert-success', "Usuário $username: alterado expiração da senha!");
            return redirect()->back();
        }

        // evita que caia em tela vazia caso nada tenha sido alterado pelo usuário
        $request->session()->flash('alert-success', "Nenhuma operação realizada.");
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $username)
    {
        $this->authorize('gerente');

        $attr = LdapUser::delete($username);

        $request->session()->flash('alert-danger', 'Usuário(a) ' . $username . ' deletado');
        return redirect()->back();
    }

    public function syncReplicadoForm(Request $request)
    {
        $this->authorize('gerente');

        $vinculos = Pessoa::tiposVinculos(config('web-ldap-admin.replicado_unidade'));
        foreach ($vinculos as &$vinculo) {
            $vinculo['countReplicado'] = Pessoa::ativosVinculo($vinculo['tipvinext'], config('web-ldap-admin.replicado_unidade'), 1)[0]['total'];
            $vinculo['countAD'] = count(LdapUser::getUsersGroup($vinculo['tipvinext']));
            $vinculo['style'] = ($vinculo['countAD'] < $vinculo['countReplicado']) ? 'text-danger' : '';
        }

        return view('ldapusers.sync', compact('vinculos'));
    }

    public function syncReplicado(Request $request)
    {
        $this->authorize('gerente');
        $this->validate($request, [
            'type' => 'required',
        ]);

        SincronizaReplicado::dispatch($request->type);
        $request->session()->flash('alert-success', 'Sincronização em andamento');
        return redirect('/ldapusers');
    }

    public function addGroup(Request $request)
    {
        $this->authorize('gerente');
        // Validações
        $request->validate([
            'username' => ['required'],
            'grupos' => ['required'],
        ]);
        $grupos = $request->grupos;
        $user = LdapUser::obterUserPorUsername($request->username);
        foreach ($grupos as $grupo) {
            $group = LdapGroup::createOrUpdate($grupo);
            $group->addMember($user);
        }
        $request->session()->flash('alert-success', 'Grupo(s) adicionado(s) com sucesso.');
        return redirect("/ldapusers/$request->username");
    }

}
