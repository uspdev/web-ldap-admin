@extends('master')

@section('content_header')
    <h1>Sincronizar com replicado</h1>
@stop

@section('content')

<div class="row">
    @include('alerts')

        <div class="col-md-6">
                       
            <form method="post" action="/ldapusers/sync">
                {{ csrf_field() }}
                <table class="table">
                    <tr>
                        <th>&nbsp;</th>
                        <th>Vínculo</th>
                        <th>Replicado</th>
                        {{-- <th>&nbsp;</th>
                        <th>AD</th> --}}
                    </tr>
                    @foreach (Uspdev\Replicado\Pessoa::tiposVinculos(config('web-ldap-admin.replicado_unidade')) as $vinculo)
                    <tr>
                    <td><input type="checkbox" name="type[]" value="{{ $vinculo['tipvinext'] }}"></td>
                        <td>{{ $vinculo['tipvinext'] }}</td>
                        <td style="text-align: right;">
                            {{ count(Uspdev\Replicado\Pessoa::ativosVinculo($vinculo['tipvinext'], config('web-ldap-admin.replicado_unidade'))) }}</td>
                        {{-- <td>>></td>
                        <td style="text-align: right;">999</td> --}}
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
