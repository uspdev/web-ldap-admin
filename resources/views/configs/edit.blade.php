@extends('layouts.app')

@section('content_header')
    <h1></h1>
@stop

@section('content')

<div class="card">
    <div class="card-header">
        Configurações
    </div>
    <div class="card-body">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Sincronizar</h5>
                <p class="card-text"><a href="{{ url('/ldapusers/sync') }}" class="btn btn-warning">Sincronizar com replicado</a></p>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Permitir pessoas sem vínculo</h5>
                <div class="row">
                    <div class="col-md-6">
                        <form method="post" action="{{ url('/configs') }}">
                            {{ csrf_field() }}
                            <div class="form-group">
                                <label for="nome">Números USP permitidos de pessoas sem vínculo com a unidade</label>
                                <textarea class="form-control" name="codpes_sem_vinculo" required>{{$codpes_sem_vinculo}}</textarea>
                            </div>
                            <div class="form-group">
                              <input type="submit" class="btn btn-primary" value="Enviar">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @if (config('web-ldap-admin.sincLdapLogin') == 1 && config('web-ldap-admin.syncGroupsWithReplicado') == 'yes')
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Fixar os grupos</h5>
                    <p class="card-text">
                        Habilitando as variáveis <code>SINC_LDAP_LOGIN</code> e <code>SYNC_GROUPS_WITH_REPLICADO</code>
                        no arquivo <code>.env</code> na sincronização com o replicado o usuário não será removido dos grupos:
                        <ul>
                        @php $grupos = (!empty(config('web-ldap-admin.notRemoveGroups'))) ? explode(',', config('web-ldap-admin.notRemoveGroups')) : []; @endphp
                        @forelse ($grupos as $grupo)
                            <li>{{ $grupo }}</li>
                        @empty
                            <li><strong>Nenhum grupo fixado.</strong> Os grupos podem ser fixados na variável <code>NOT_REMOVE_GROUPS</code>.</li>
                        @endforelse
                        </ul>
                    </p>
                </div>
            </div>
        @endif
    </div>
 </div>

 @stop
