@extends('layouts.app')

@section('title', 'Meus Dados')

@section('content_header')
    <h1></h1>
@stop

@section('content')

    <div class="h4">
        Username: {{ $attr['username'] ?? '' }}
        <span class="badge">@include('ldapusers.partials.expiry',['label'=>true])</span>
        <span class="badge">@include('ldapusers.partials.enabled',['label'=>true])</span>
        <span class="badge">@include('ldapusers.partials.delete')</span>
    </div>

    <div class="row">
        <div class="col-md-6">
            <table class="table table-striped">
                <tbody>
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
                        <td> <b> Descrição </b> </td>
                        <td>{{ $attr['description'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <td><b>Nro. USP</b></td>
                        <td>{{ $attr['codpes'] ?? '' }}</td>
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
                        <td> <b> Data da última alteração da senha </b> </td>
                        <td> {{ $attr['senha_alterada_em'] ?? '' }} </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            @foreach ($vinculos as $vinculo)
                @switch($vinculo['tipvin'])
                    @case('ALUNOPOS')
                        <h4>Vínculo: {{ $vinculo['tipvinext'] }}</h4>
                        <table class="table table-sm table-striped">
                            <tr>
                                <td>Orientador</td>
                                <td>{{ $vinculo['nompesori'] }}</td>
                            </tr>
                            <tr>
                                <td>Programa</td>
                                <td>{{ $vinculo['nomcur'] }} - nível {{ $vinculo['nivpgm'] }} </td>
                            </tr>
                            <tr>
                                <td>Situação</td>
                                <td>{{ $vinculo['sitoco'] }}</td>
                            </tr>
                            <tr>
                                <td>Ingresso</td>
                                <td>{{ $vinculo['dtainivin'] }} </td>
                            </tr>
                        </table>
                    @break
                @endswitch
            @endforeach
        </div>
    </div>


    @if ($user->isEnabled())
        <h4> Editar </h4>

        <div class="row">
            <div class="col-sm-4">
                <form method="POST" action="{{ url('/ldapusers/' . $attr['username']) }}">
                    @csrf
                    @method('patch')

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
    @endif
@endsection
