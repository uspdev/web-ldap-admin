@extends('laravel-usp-theme::master')

@section('title', config('app.name'))

@section('content_header')
    <h1></h1>
@stop

@section('content')
@include('alerts')
    @auth
        Acesse o menu acima com as opções
    @else
        Você ainda não fez seu login com a senha única USP <a href="{{ url('/login') }}"> Faça seu Login! </a>
    @endauth
@stop


