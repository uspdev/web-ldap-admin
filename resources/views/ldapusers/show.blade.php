@extends('layouts.app')

@section('title', 'Meus Dados')

@section('content')

  <div class="h4">
    Username: {{ $attr['username'] ?? '' }}
    <span class="badge">@include('ldapusers.partials.show-expiry')</span>
    <span class="badge">@include('ldapusers.partials.show-enabled')</span>
    <span class="badge">@includeWhen(Gate::check('gerente'),'ldapusers.partials.show-delete')</span>
  </div>

  <div class="row">
    <div class="col-md-12">

      {{-- mensagem para o aluno informando sobre conta desabilitada --}}
      @if (!Gate::check('gerente') && $user->isDisabled())
        <div class="alert alert-danger" role="alert">
          Sua conta está desabilitada. Entre em contato com o administeador da rede para habilitar a conta.
        </div>
      @endif

      {{-- mensagem para o gerente avisando sobre conta ldap não vinculada ao aluno --}}
      @if (Gate::check('gerente') && $attr['codpes'] && !$codpesValido)
        <div class="alert alert-warning" role="alert">
          O número USP está presente mas não no campo indicado pelo config
          (campoCodpes={{ config('web-ldap-admin.campoCodpes') }}). 
          O usuário, ao efetuar login, não vai ter essa conta associada a ele!
        </div>
      @endif

    </div>
  </div>

  <div class="row">
    <div class="col-md-6">
      @include('ldapusers.partials.show-basic')
    </div>
    <div class="col-md-6">
      @includeWhen(Gate::check('gerente'),'ldapusers.partials.show-vinculos')
      @includeWhen($user->isEnabled(),'ldapusers.partials.show-pwd-form')
    </div>
  </div>

@endsection
