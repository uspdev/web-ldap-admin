@extends('laravel-usp-theme::master')

@section('content_header')
    <h1>Cadastrar usuário</h1>
@stop

@section('content')

<div class="row">
    @include('alerts')

        <div class="col-md-6">
            <form method="post" action="{{ url('/ldapusers') }}">
                {{ csrf_field() }}
                @include('ldapusers.form')
            </form>
        </div>
    </div>

@stop
