@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
    @stop

    @section('content')

    @auth
        Logado
        <a href="/logout">Logout</a>
    @else
        Não logado <br>
        <a href="/login/senhaunica">Login</a>
    @endauth
@stop

