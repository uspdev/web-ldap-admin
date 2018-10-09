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

        // Filtrar por grupo
        // $externos = Adldap::search()->groups()->find('externos');

        // paginação não funcionou
        //$ldapusers = Adldap::search()->users()->paginate(50)->getResults();

        $ldapusers = Adldap::search()->users();

        // remove usuários do sistema da lista
        $ldapusers = $ldapusers->where('samaccountname','!=','Administrator');
        $ldapusers = $ldapusers->where('samaccountname','!=','krbtgt');
        $ldapusers = $ldapusers->where('samaccountname','!=','Guest');

        $ldapusers = $ldapusers->get(); 
        //dd($ldapusers);
        return view('ldapusers.index',compact('ldapusers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        SincronizaReplicado::dispatch();

        $request->session()->flash('alert-info', 'Sincronização em andamento');
        return redirect('/ldapusers');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //$this->authorize('ldapusers.show', $id);
        $attr = LdapUser::show($id);
        return view('ldapusers.show',compact('attr'));
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
    public function update(Request $request, $id)
    {

        //$this->authorize('ldapusers.update', $id);

        // troca de senha
        if(!is_null($request->senha)) {
            $request->validate([
               'senha' => 'required|min:8',
            ]);
     
            if($request->senha != $request->repetir_senha){
                $request->session()->flash('alert-danger', 'As senhas digitadas não são iguais, senha não alterada!');
                return redirect('/ldapusers/' . $id);    
            }
            
            LdapUser::changePassword($id,$request->senha);
            $request->session()->flash('alert-success', 'Senha alterada com sucesso!');
            return redirect('/ldapusers/' . $id);          
        }

        // status
        if(!is_null($request->status)) {

            if($request->status == 'disable') {
                LdapUser::disable($id);
                $request->session()->flash('alert-success', 'Usuário Desabilitado');
            return redirect('/ldapusers/' . $id);  
            }

            if($request->status == 'enable') {
                LdapUser::enable($id);
                $request->session()->flash('alert-success', 'Usuário Habilitado');
            return redirect('/ldapusers/' . $id);  
            }                     
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $attr = LdapUser::delete($id);

        $request->session()->flash('alert-danger', 'Usuário(a) deletado');
        return redirect('/ldapusers');
    }
}
