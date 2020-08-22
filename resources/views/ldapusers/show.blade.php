@extends('laravel-usp-theme::master')

@section('title', 'Meus Dados')

@section('content_header')
<h1></h1>
@stop

@section('content')
@include('alerts')

@isset($attr['msg'])

<div class="panel panel-default">
  <div class="panel-body">{{ $attr['msg'] }}</div>
</div>

@else
    <table class="table table-striped">
        <tbody>

            <tr>
                <td> <b>Username</b> </td>
                <td>{{ $attr['username'] ?? '' }}</td>
            </tr>

            <tr>
                <td> <b>Seu nome </b> </td>
                <td>{{ $attr['display_name'] ?? '' }}</td>
            </tr>

            <tr>
                <td> <b> Email</b> </td>
                <td>{{ $attr['email'] ?? '' }}</td>
            </tr>

            <tr>
                <td> <b> Grupos </b> </td>
                <td>{{ $attr['grupos'] ?? '' }}</td>
            </tr>

            <tr>
                <td> <b> Departamento </b> </td>
                <td>{{ $attr['department'] ?? '' }}</td>
            </tr>

            <tr>
                <td> <b> Conta criada em </b> </td>
                <td>{{ $attr['ativacao'] ?? '' }}</td>
            </tr>

            <tr>
                <td> <b> Essa conta expira em </b> </td>
                <td>{{ $attr['expira'] ?? "Não expira" }}</td>
            </tr>

            <tr>
                <td> <b> Data da última alteração da senha </b> </td>
                <td> {{ $attr['senha_alterada_em'] ?? '' }} </td>
            </tr>

            <tr>
                <td> <b> Status </b> </td>
                <td> {{ $attr['status'] ?? '' }} </td>
            </tr>

        </tbody>
    </table>

    <h2> Editar </h2>

    <div class="row">
        <div class="col-sm-4">
            <form method="POST" action="{{ url('/ldapusers/'.$attr['username']) }}">
                {{csrf_field()}}
                {{ method_field('PATCH') }}

                <div class="form-group">
                  <label for="usr"> Nova senha:</label>
                  <input type="password" class="form-control" name="senha">
                  <i> Mínimo de 8 caracteres. </i>
                </div>

                <div class="form-group">
                  <label for="usr"> Repetir Nova senha:</label>
                  <input type="password" class="form-control" name="senha_confirmation">
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-success"> Altera Senha </button>
                </div>
            </form>
        </div>
    </div>
@endisset

@stop

