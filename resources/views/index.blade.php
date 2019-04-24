@extends('adminlte::page')

@section('title', 'USP')

@section('content_header')
    <h1></h1>
@stop

@section('content')
@include('alerts')
    @auth
        <h3><b>Olá {{ Auth::user()->name }},</b></h3>
        Acesse a <a href="/ldapusers/my"> área restrita </a> para definir ou alterar sua <i>senha</i>
    @else
        Você ainda não fez seu login com a senha única USP <a href="/login"> Faça seu Login! </a>
    @endauth
@stop

