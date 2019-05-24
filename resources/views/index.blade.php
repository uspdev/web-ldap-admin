@extends('master')

@section('title', 'USP')

@section('content_header')
    <h1></h1>
@stop

@section('content')
@include('alerts')
    @auth
        <script>window.location = "/ldapusers/my";</script>
    @else
        Você ainda não fez seu login com a senha única USP <a href="/login"> Faça seu Login! </a>
    @endauth
@stop


