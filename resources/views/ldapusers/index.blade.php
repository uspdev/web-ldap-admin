@extends('layouts.app')

@section('content')
  <div class="card card-primary">
    <div class="card-header">
      <span class="pr-3 h4">Filtros</span>

      @if ($request->search)
        <button type="button" class="btn btn-sm btn-outline-dark" disabled>
          Busca: <strong>{{ $request->search }}</strong>
        </button>
      @endif

    </div>
    <div class="card-body">
      <form method="get" action="ldapusers?page={{ $nav['pagCor'] }}&perPage{{ $nav['perPag'] }}">
        <div class="row">

          <div class="col-md-4">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon1">Buscar</span>
              </div>
              <input type="text" class="form-control" placeholder="por nome ou username..." name="search">
            </div>
          </div>

          <div class="col-md-4">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon1">Grupos</span>
              </div>
              <select class="select2 form-control" name="grupos[]" multiple="multiple">
                @foreach ($grupos as $grupo)
                  <option value="{{ $grupo }}" {{ in_array($grupo, $request->grupos ?? []) ? 'selected' : '' }}>
                    {{ $grupo }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="col-md-4">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon1">Listando</span>
              </div>
              <select class="form-control" name="perPage" placeholder="teste">
                @for ($totPerPage = 50; $totPerPage <= 1000; $totPerPage += 50)
                  <option value="{{ $totPerPage }}" {{ $nav['perPag'] == $totPerPage ? 'selected' : '' }}>
                    {{ $totPerPage }} registros por página
                  </option>
                @endfor
              </select>
            </div>
          </div>

        </div>
        <button type="submit" class="btn btn-success mt-3">Buscar</button>

      </form>
    </div>
  </div>

  {{-- Paginação --}}
  @include('ldapusers.partials.nav')
  <div class="table-responsive mt-3">

    <table class="table table-sm table-striped table-hover datatables">
      <thead>
        <tr>
          <th>Username</th>
          <th>Nro USP</th>
          <th>Nome</th>
          <th>Email</th>
          <th>Descrição</th>
          <th>Grupos</th>
          <th>Conta</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($ldapusers as $user)
          <tr>
            <td>
              <a href="ldapusers/{{ $user->getFirstAttribute('samaccountname') }}"> {{ $user->getFirstAttribute('samaccountname') }}</a>
            </td>
            <td> {{ App\Ldap\User::obterCodpes($user) }} </td>
            <td> {{ $user->getFirstAttribute('displayname') }} </td>
            <td> {{ $user->getFirstAttribute('mail') }} </td>
            <td> {{ $user->getFirstAttribute('description') }} </td>
            <td>
              {{ implode(', ', array_diff(\App\Ldap\User::getGroupNames($user), ['Domain Users'])) }}
            </td>
            <td class="text-center">
              @include('ldapusers.partials.expiry')
            </td>
            <td class="text-center">
              @include('ldapusers.partials.enabled')
            </td>
            <td class="text-center">
              @include('ldapusers.partials.delete')
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  {{-- Paginação --}}
  @include('ldapusers.partials.nav')
@endsection

@section('javascripts_bottom')
  @parent
  <script type="text/javascript">
    $(document).ready(function() {

      oTable = $('.datatables').DataTable({
        dom: 't',
        "paging": false,
        "sort": true,
        "order": [
          [0, "asc"]
        ]
      })

      $(".select2").select2({
        placeholder: "Selecione o(s) grupo(s)"
      })
    })
  </script>
@endsection
