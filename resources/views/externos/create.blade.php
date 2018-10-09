@extends('adminlte::page')

@section('content_header')
    <h1>Cadastrar visitante</h1>
@stop

@section('content')

<div class="row">
    @include('alerts')

        <div class="col-md-6">
            <form method="post" action="{{ url('externos') }}">
                {{ csrf_field() }}
                @include('externos.form')
            </form>
        </div>
    </div>

@stop
