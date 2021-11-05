@extends('laravel-usp-theme::master')

@section('content_header')
    <h1>Sincronizar com replicado</h1>
@stop

@section('content')

<div class="row">
    @include('alerts')

        <div class="col-md-6">
                       
            <form method="post" action="{{ url('/ldapusers/sync') }}">
                {{ csrf_field() }}
                <table class="table table-striped">
                    <tr>
                        <th>&nbsp;</th>
                        <th>Vínculo</th>
                        <th style="text-align: right;">Replicado</th>
                        <th style="text-align: right;">AD</th>
                    </tr>

                    @foreach (Uspdev\Replicado\Pessoa::tiposVinculos(config('web-ldap-admin.replicado_unidade')) as $vinculo)
                    <tr>
                    <td><input type="checkbox" name="type[]" value="{{ $vinculo['tipvinext'] }}"></td>
                        <td>{{ $vinculo['tipvinext'] }}</td>
                        <td style="text-align: right;">
                            {{ Uspdev\Replicado\Pessoa::ativosVinculo($vinculo['tipvinext'], config('web-ldap-admin.replicado_unidade'), 1)[0]['total'] }}
                        </td>
                        <td style="text-align: right;">
                            {{ count(App\Ldap\User::getUsersGroup($vinculo['tipvinext'])) }}
                        </td>
                    </tr>
                    @endforeach                    
                </table>

                <div class="form-group">
                  <input type="submit" class="btn btn-primary" value="Sincronizar">
                </div>
            </form>
        </div>
    </div>

@stop
