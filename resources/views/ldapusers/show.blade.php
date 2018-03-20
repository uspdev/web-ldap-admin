@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
<h1></h1>
@stop

@section('content')

    <table class="table table-striped">
        <tbody>

            <tr>
                <td> <b>Seu nome </b> </td>
                <td>{{ $attr['display_name'] }}</td>
            </tr>

            <tr>
                <td> <b> Email</b> </td>
                <td>{{ $attr['email'] }}</td>
            </tr>

            <tr>
                <td> <b> Quota </b> </td>
                <td>{{ $attr['quota'] }} GB </td>
            </tr>

            <tr>
                <td> <b> Drive </b> </td>
                <td>{{ $attr['drive'] }}</td>
            </tr>

            <tr>
                <td> <b> Diretório no servidor </b> </td>
                <td>{{ $attr['dir'] }}</td>
            </tr>

            <tr>
                <td> <b> Conta criada em </b> </td>
                <td>{{ $attr['ativacao'] }}</td>
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

<h2> Troque sua senha</h2>

<form>
<div class="form-group">
  <label for="usr"> Nova senha:</label>
  <input type="password" class="form-control" id="usr">
</div>

<div class="form-group">
  <label for="usr"> Repetir Nova senha:</label>
  <input type="password" class="form-control" id="usr">
</div>

<div class="form-group">
    <button type="submit" class="btn btn-success"> Altera senha </button>
</div>
</form>
@stop

