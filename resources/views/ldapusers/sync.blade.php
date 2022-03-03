@extends('layouts.app')

@section('content_header')
    <h1>Sincronizar com replicado</h1>
@stop

@section('content')

    <div class="row">
        <div class="col-md-6">

            <form method="post" action="{{ url('/ldapusers/sync') }}">
                @csrf
                <table class="table table-striped">
                    <tr>
                        <th>&nbsp;</th>
                        <th>Vínculo</th>
                        <th class="text-right">Replicado</th>
                        <th class="text-right">{{ config('web-ldap-admin.ouDefault') }}</th>
                    </tr>
                    @foreach ($vinculos as $vinculo)
                        <tr>
                            <td>
                                <input type="checkbox" name="type[]" value="{{ $vinculo['tipvinext'] }}">
                            </td>
                            <td>
                                {{ $vinculo['tipvinext'] }}
                            </td>
                            <td class="text-right">
                                {{ $vinculo['countReplicado'] }}
                            </td>
                            <td class="text-right {{ $vinculo['style'] }}">
                                {{ $vinculo['countAD'] }}
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
