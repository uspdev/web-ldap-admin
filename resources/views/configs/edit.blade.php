@extends('layouts.app')

@section('content_header')
    <h1></h1>
@stop

@section('content')
<a href="{{ url('/ldapusers/sync') }}" class="btn btn-warning">Sincronizar com replicado</a>
<br><br>

<div class="row">
        <div class="col-md-6">
            <form method="post" action="{{ url('/configs') }}">
                {{ csrf_field() }}

                <div class="form-group">
                    <label for="nome">Números USP permitidos de pessoas sem vínculo com a unidade</label>
                    <textarea rows="8" cols="90" name="codpes_sem_vinculo" required>{{$codpes_sem_vinculo}}</textarea>
                </div>

                <div class="form-group">
                  <input type="submit" class="btn btn-primary" value="Enviar">
                </div>
                
            </form>
        </div>
    </div>

@stop
