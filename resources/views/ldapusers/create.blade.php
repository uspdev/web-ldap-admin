@extends('layouts.app')

@section('content_header')
  <h1>Cadastrar usuário</h1>
@stop

@section('content')

  <div class="row">
    <div class="col-md-6">
      <form method="post" action="{{ url('/ldapusers') }}">
        {{ csrf_field() }}
        @include('ldapusers.partials.create-form')
      </form>
    </div>
  </div>

@stop
