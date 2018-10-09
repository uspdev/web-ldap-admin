<?php

namespace App\Http\Controllers;

use App\Externo;
use Illuminate\Http\Request;

use App\Ldap\User as LdapUser;
use App\Ldap\Group as LdapGroup;
use Carbon\Carbon;

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
            'nome'      => 'required',
            'email'      => 'required|email',
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

        $request->session()->flash('alert-success', 'Rede cadastrada com sucesso!');
        return redirect("/externos/{$externo->id}");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Externo  $externo
     * @return \Illuminate\Http\Response
     */
    public function show(Externo $externo)
    {
        //
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
    public function destroy(Externo $externo)
    {
        //
    }
}
