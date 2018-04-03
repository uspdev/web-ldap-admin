@extends('adminlte::page')

@section('title', 'Senha FLFCH')

@section('content_header')
    <h1></h1>
@stop

@section('content')
@include('alerts')
    @auth
        <h3><b>Olá {{ Auth::user()->name }},</b></h3>
        Acesse sua <a href="/ldapusers"> área restrita </a> para definir ou alterar a <i>senha FFLCH</i>
    @else
        Você ainda não fez seu login com a senha única USP <a href="/login"> Faça seu Login! </a>
    @endauth
    <h3>Com a senha FFLCH você:</h3>
    <ul>
        <li>Acessa os computadores no domínio FFLCH</li>
        <li>Gerencia os sites que você administra</li>
        <li>Abre <a href="http://www.sisinfo.fflch.usp.br/">chamados </a> para a Seção Técnica de Informática</li>
    </ul>
 
    
@stop

