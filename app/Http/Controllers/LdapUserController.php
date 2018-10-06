<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Ldap\User as LdapUser;
use Carbon\Carbon;
use Adldap\Laravel\Facades\Adldap;

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
    public function index()
    {
        // paginação nõa funcionou
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
    public function create()
    {
        $attr = LdapUser::createOrUpdate('dwdwq');
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $attr = LdapUser::delete($id);
        return redirect('/ldapusers');
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
