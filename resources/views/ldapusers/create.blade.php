@extends('layouts.app')

@section('content_header')
  <h1>Cadastrar usuário</h1>
@stop

@section('content')

  <div class="row">
    <div class="col-md-6">
      <form method="post" action="{{ url('/ldapusers') }}">
        @csrf
        @include('ldapusers.partials.create-form')
      </form>
    </div>
    <div class="col-md-6">
      <form method="post" action="{{ url('/ldapusers') }}">
        @csrf
        <div class="form-group">
          <label for="criar-por-codpes">Criar a partir do número USP</label>
          <input type="text" name="codpes" class="form-control" id="criar-por-codpes" placeholder="Número USP ..">
        </div>
        <button class="btn btn-primary" type="submit" name="acao" value="criar-por-codpes">Enviar</button>
      </form>
    </div>
  </div>

@stop
