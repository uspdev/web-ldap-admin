<?php

namespace App\Http\Controllers;

use App\Externo;
use Illuminate\Http\Request;

use Adldap\Laravel\Facades\Adldap;
use App\Ldap\User as LdapUser;
use App\Ldap\Group as LdapGroup;
use Carbon\Carbon;

use App\Rules\LdapEmailRule;

class ExternoController extends Controller
{

    public function __construct()
    {
        //$this->middleware('can:admin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $externos = Externo::all();
        
        // verifica se usuário existe no ldap
        foreach($externos as $externo) {
            $check = Adldap::search()->users()->find('e'.$externo->id);
            if(is_null($check)){
                $externo['ldap'] = 'não';        
            } else {
                $externo['ldap'] = 'sim'; 
            }
        }
        return view('externos.index')->with('externos', $externos);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('externos.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validações
        $request->validate([
            'nome'      => 'required|regex:/(^([a-zA-Z]+))/u',
            'email'      => ['required','email', new LdapEmailRule],
        ]);

        $externo = new Externo;
        $externo->nome = $request->nome;
        $externo->email = $request->email;
        $externo->motivo = $request->motivo;
        $externo->save();

        // Falta enviar a data de vencimento para desabilitar no ldap
        LdapUser::createOrUpdate('e' . $externo->id, [
            'nome' => $externo->nome,
            'email' => $externo->email
        ],
        'externos');

        $request->session()->flash('alert-success', 'Usuário cadastrado com sucesso!');
        return redirect("/ldapusers/e{$externo->id}");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Externo  $externo
     * @return \Illuminate\Http\Response
     */
    public function show(Externo $externo)
    {
        return redirect("/ldapusers/e{$externo->id}");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Externo  $externo
     * @return \Illuminate\Http\Response
     */
    public function edit(Externo $externo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Externo  $externo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Externo $externo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Externo  $externo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,Externo $externo)
    {
        // deleta no ldap
        $attr = LdapUser::delete('e'.$externo->id);

        // deleta localmente
        $externo->delete();

        $request->session()->flash('alert-danger', 'Usuário(a) deletado localmente e do ldap');
        return redirect('/externos');
    }
}
