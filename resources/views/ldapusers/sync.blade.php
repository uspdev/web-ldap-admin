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
                <input type="checkbox" name="type[]" value="servidores"> Servidores<br>
                <input type="checkbox" name="type[]" value="estagiarios"> Estagiários<br>            

                <div class="form-group">
                  <input type="submit" class="btn btn-primary" value="Sincronizar">
                </div>
            </form>
        </div>
    </div>

@stop
