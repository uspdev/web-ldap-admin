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
                    <div class="col-md-auto">
                        <form id="nrousp" method="post" action="{{ url('/configs') }}">
                            {{ csrf_field() }}
                            <div class="form-group">
                                <label for="nome">Números USP permitidos de pessoas sem vínculo com a unidade</label>
                                <textarea rows="{{ count(explode(',', $codpes_sem_vinculo)) }}" placeholder="Digite um número USP por linha"
                                    class="form-control text-right" name="codpes_sem_vinculo" id="codpes_sem_vinculo"
                                    required>{{ str_replace(',', "\r\n", $codpes_sem_vinculo) }}</textarea>
                                <span class="text-danger">
                                    Digite um Número USP por linha.<br />
                                    Crie a conta manualmente no AD para as pessoas com <strong>Dados não encontrados</strong>.
                                </span>
                            </div>
                            <div class="form-group">
                              <input type="submit" class="btn btn-primary" value="Enviar">
                            </div>
                        </form>
                    </div>
                    <div class="col-md-auto">
                        <div class="form-group">
                            Números USP, nome, unidade e vínculo na USP. Tabela <code>CATR_CRACHA</code>.
                            <ul class="mt-2">
                                @foreach (explode(',', $codpes_sem_vinculo) as $codpes)
                                <li>
                                    {{ $codpes }} -
                                    @if (Uspdev\Replicado\Pessoa::cracha($codpes))
                                        {{ Uspdev\Replicado\Pessoa::cracha($codpes)['nompescra'] }} -
                                        {{ Uspdev\Replicado\Pessoa::cracha($codpes)['nomorg'] }} -
                                        {{ Uspdev\Replicado\Pessoa::cracha($codpes)['tipvinaux'] }}
                                    @else
                                        <strong>Dados não encontrados</strong>
                                    @endif
                                </li>
                                @endforeach
                            </ul>
                        </div>
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

@section('javascripts_bottom')
  @parent
  <script type="text/javascript">
    $(function() {
        $('#nrousp').submit(function() {
            var campo = $('#codpes_sem_vinculo');
            campo.val( $.trim(campo.val()).replace(/\s+/g, ',') );
            return true;
        });
    });
  </script>
@endsection