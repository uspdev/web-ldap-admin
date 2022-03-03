@extends('layouts.app')

@section('content')
    @php
    // Informações para paginar
    // $totPag = $ldapusers->getPages(); # Total de páginas
    // $maxLnk = 5; # Máximo de links
    // $pagCor = $ldapusers->getCurrentPage(); # Página atual
    // $lnkLat = ceil($maxLnk / 2); # Cálculo dos links laterais
    // $pagIni = $pagCor - $lnkLat; # Início dos links (esquerda)
    // $pagFin = $pagCor + $lnkLat; # Fim dos links (direita)
    // $offSet = $ldapusers->getCurrentOffSet(); # offSet
    // $perPag = (!empty($request->perPage) && isset($request->perPage)) ? $request->perPage : $ldapusers->getPerPage(); # Registros por página
    // $regIni = ($offSet - $perPag); # Registro inicial
    // $regFin = ($offSet > $ldapusers->count()) ? $ldapusers->count() : $offSet; # Registro final
    // Filtros de busca
    $searchGrupos = '';
    $gruposUrl = '';
    if (!empty($request->grupos) && isset($request->grupos)) {
        $searchGrupos = implode(', ', $request->grupos);
        foreach ($request->grupos as $grupo) {
            $gruposUrl .= "&grupos[]=$grupo";
        }
    }
    @endphp

    {{-- <a href="{{ url('/ldapusers/create') }}" class="btn btn-success">Criar usuário não replicado</a> --}}
    {{-- <br><br> --}}

    <div class="card card-primary">
        <div class="card-header">
            <span class="pr-3 h4">Filtros</span>
            <button type="button" class="btn btn-sm btn-outline-dark" disabled>
                Grupos: <strong>{{ $searchGrupos }}</strong>
            </button>
            <button type="button" class="btn btn-sm btn-outline-dark" disabled>
                Busca: <strong>{{ $request->search }}</strong>
            </button>
            <button type="button" class="btn btn-sm btn-outline-dark" disabled>
                Listando <strong>{{ $perPag }}</strong> registros por página
            </button>
            <button type="button" class="btn btn-sm btn-outline-dark" disabled>
                Total de registros: <strong>{{ count($ldapusers) }}</strong>
            </button>
        </div>
        <div class="card-body">
            <form method="get" action="ldapusers?page={{ $pagCor }}&perPage{{ $perPag }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Buscar..." name="search">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="select2 form-control" name="grupos[]" multiple="multiple">
                            @foreach ($grupos as $grupo)
                                <option value="{{ $grupo }}">{{ $grupo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <select class="form-control" name="perPage" placeholder="teste">
                                <option value="{{ $perPag }}">Selecione a quantidade de registros por página</option>
                                @for ($totPerPage = 50; $totPerPage <= 1000; $totPerPage += 50)
                                    <option value="{{ $totPerPage }}">{{ $totPerPage }} registros por página</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-success">Buscar</button>
            </form>
        </div>
    </div>

    {{-- Paginação --}}
    {{-- @include('ldapusers.nav') --}}
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
                    <th>Expiração</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ldapusers as $user)
                    <tr>
                        <td>
                            <a href="ldapusers/{{ $user->getAccountName() }}"> {{ $user->getAccountName() }}</a>
                        </td>
                        <td> {{ App\Ldap\User::obterCodpes($user) }} </td>
                        <td> {{ $user->getDisplayName() }} </td>
                        <td> {{ $user->getEmail() }} </td>
                        <td> {{ $user->getDescription() }} </td>
                        <td>
                            {{ implode(', ', array_diff($user->getGroupNames(), ['Domain Users'])) }}
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
    {{-- @include('ldapusers.nav') --}}
@endsection

@section('javascripts_bottom')
    @parent
    <script type="text/javascript">
        $(document).ready(function() {

            // oTable = $('.datatables').DataTable({
            //     dom: 't',
            //     "paging": false,
            //     "sort": true,
            //     "order": [
            //         [0, "asc"]
            //     ]
            // })

            $(".select2").select2({
                placeholder: "Selecione o(s) grupo(s)"
            })
        })
    </script>
@endsection
