@extends('adminlte::page')

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
                <td> <b>Seu nome </b> </td>
                <td>{{ $attr['display_name'] or '' }}</td>
            </tr>

            <tr>
                <td> <b> Email</b> </td>
                <td>{{ $attr['email'] or '' }}</td>
            </tr>

            <tr>
                <td> <b> Quota </b> </td>
                <td>{{ $attr['quota'] or '' }} GB </td>
            </tr>

            <tr>
                <td> <b> Drive </b> </td>
                <td>{{ $attr['drive'] or '' }}</td>
            </tr>

            <tr>
                <td> <b> Diretório no servidor </b> </td>
                <td>{{ $attr['dir'] or '' }}</td>
            </tr>

            <tr>
                <td> <b> Grupos </b> </td>
                <td>{{ implode(", ",$attr['grupos']) or '' }}</td>
            </tr>

            <tr>
                <td> <b> Conta criada em </b> </td>
                <td>{{ $attr['ativacao'] or '' }}</td>
            </tr>

            <tr>
                <td> <b> Essa conta expira em </b> </td>
                <td>{{ $attr['expira'] or "Não expira" }}</td>
            </tr>

            <tr>
                <td> <b> Data da última alteração da senha FFLCH </b> </td>
                <td> {{ $attr['senha_alterada_em'] }} </td>

            </tr>

        </tbody>
    </table>

    <h2> Trocar senha </h2>

    <div class="row">
        <div class="col-sm-3">
            <form method="POST" action="/ldapusers">
            @csrf
            <div class="form-group">
              <label for="usr"> Nova senha:</label>
              <input type="password" class="form-control" name="senha">
              <i> Mínimo de 8 caracteres</i>
            </div>

            <div class="form-group">
              <label for="usr"> Repetir Nova senha:</label>
              <input type="password" class="form-control" name="repetir_senha">
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-success"> Altera Senha </button>
            </div>
            </form>
        </div>
    </div>
@endisset

@stop

