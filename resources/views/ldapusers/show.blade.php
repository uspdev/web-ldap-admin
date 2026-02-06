@extends('layouts.app')

@section('title', 'Meus Dados')

@section('content')

  <div class="h4">
    Username: {{ $attr['username'] ?? '' }}
    <span class="badge">@include('ldapusers.partials.show-expiry')</span>
    <span class="badge">@include('ldapusers.partials.show-enabled')</span>
    <span class="badge">@includeWhen(Gate::check('manager'), 'ldapusers.partials.show-delete')</span>
  </div>

  <div class="row">
    <div class="col-md-12">

      {{-- mensagem para o aluno informando sobre conta desabilitada --}}
      @if (!Gate::check('manager') && $user->isDisabled())
        <div class="alert alert-danger" role="alert">
          Sua conta está desabilitada. Entre em contato com o administeador da rede para habilitar a conta.
        </div>
      @endif

      {{-- mensagem para o manager avisando sobre conta ldap não vinculada ao aluno --}}
      @if (Gate::check('manager') && $attr['codpes'] && !$codpesValido)
        <div class="alert alert-warning" role="alert">
          O número USP está presente mas não no campo indicado pelo config
          (campoCodpes={{ config('web-ldap-admin.campoCodpes') }}).
          O usuário, ao efetuar login, não vai ter essa conta associada a ele!
        </div>
      @endif

    </div>
  </div>

  <div class="row">
    <div class="col-md-5">
      @include('ldapusers.partials.show-basic')
    </div>
    <div class="col-md-5">
      @includeWhen(Gate::check('manager'), 'ldapusers.partials.show-vinculos')
      @includeWhen(Gate::check('manager'), 'ldapusers.partials.show-new-password')
      @includeWhen($user->isEnabled(), 'ldapusers.partials.show-pwd-form')
    </div>
    <div class="col-md-2">
      @if (config('web-ldap-admin.mostrarFoto') == 1 && $foto != '')
        <div><b>Foto cartão USP</b></div>
        <div><img style="float: left; width: 100%" src="data:image/png;base64, {{ $foto }}" alt="foto"></div>
      @endif
    </div>
  </div>

@endsection

@section('javascripts_bottom')
  @parent
  <script type="text/javascript">
    $(document).ready(function() {
      $('#addGroup').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget) // Button that triggered the modal
        var recipient = button.data('whatever') // Extract info from data-* attributes
        var modal = $(this)
        modal.find('.modal-body h5').text(recipient)
        $("#grupos").select2({
          tags: true,
          placeholder: "Selecione o(s) grupo(s) ou digite o(s) nome(s) de novo(s) grupo(s)"
        })
      });
    });
  </script>
@endsection
