@extends('laravel-usp-theme::master')

@section('content')
@include('alerts')

@php 
    // Informações para paginar
    $totPag = $ldapusers->getPages(); # Total de páginas
    $maxLnk = 5; # Máximo de links
    $pagCor = $ldapusers->getCurrentPage(); # Página atual
    $lnkLat = ceil($maxLnk / 2); # Cálculo dos links laterais
    $pagIni = $pagCor - $lnkLat; # Início dos links (esquerda)
    $pagFin = $pagCor + $lnkLat; # Fim dos links (direita)  
    $offSet = $ldapusers->getCurrentOffSet(); # offSet
    $perPag = (!empty($request->perPage) && isset($request->perPage)) ? $request->perPage : $ldapusers->getPerPage(); # Registros por página
    $regIni = ($offSet - $perPag); # Registro inicial
    $regFin = ($offSet > $ldapusers->count()) ? $ldapusers->count() : $offSet; # Registro final
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

<a href="/ldapusers/create" class="btn btn-success">Criar usuário não replicado</a>
<br><br>

<div class="panel panel-default">
    <div class="panel-heading pb-3">
        <strong>Filtros</strong>
    </div>
    <div class="panel-body">
        <form method="get" action="/ldapusers?page={{ $pagCor }}&perPage{{ $ldapusers->getPerPage() }}">
            <div>
                <script type="text/javascript">
                    $(function () {
                        $(".select2").select2({
                            placeholder: "Selecione o(s) grupo(s)"
                        });
                    });
                </script>
                <select class="select2 form-control" name="grupos[]" multiple="multiple">
                    @foreach($grupos as $grupo)
                        <option value="{{$grupo}}">{{$grupo}}</option>
                    @endforeach
                </select>
            </div>
            <br />
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Buscar..." name="search">
            </div>
            <br />
            <div class="pb-3">
                <select class="select2 form-control" name="perPage" placeholder="teste">
                    <option value="{{ $perPag }}">Selecione a quantidade de registros por página</option>
                    @for($totPerPage = 50; $totPerPage <= 1000; $totPerPage += 50)
                        <option value="{{ $totPerPage }}">{{ $totPerPage }} registros por página</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="btn btn-success">Buscar</button>
        </form>
    </div>
    <div class="pt-3"> 
        <button type="button" class="btn btn-outline-dark" disabled>
            Grupos: <strong>{{ $searchGrupos }}</strong> 
        </button>
        <button type="button" class="btn btn-outline-dark" disabled>
            Busca: <strong>{{ $request->search }}</strong> 
        </button>      
        <button type="button" class="btn btn-outline-dark" disabled>
            Listando <strong>{{ $perPag }}</strong> registros por página 
        </button> 
        <button type="button" class="btn btn-outline-dark" disabled>   
            Total de registros: <strong>{{ $ldapusers->count() }}</strong>
        </button>
    </div>        
    {{-- Paginação --}}
    @include('ldapusers.nav')
    <div class="table-responsive">
        <script type="text/javascript">
            $(function () {
                $(".delete-item").on("click", function(){
                    return confirm("Tem certeza?");
                });
            });
        </script>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nº USP</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Grupos</th>
                    <th>Status</th>
                    <th colspan="2">Ações</th>
                </tr>
            </thead>
            <tbody>
                @for ($i = $regIni; $i < $regFin; $i++)
                <tr> 
                    <td><a href="/ldapusers/{{ $ldapusers->getResults()[$i]->getAccountName() }}"> {{ $ldapusers->getResults()[$i]->getAccountName() }}</a></td>
                    <td> {{ $ldapusers->getResults()[$i]->getDisplayName() }} </td>
                    <td> {{ $ldapusers->getResults()[$i]->getEmail() }} </td>
                    <td><?php 
                            $grupos = array_diff($ldapusers->getResults()[$i]->getGroupNames(),['Domain Users']);
                            $grupos = implode(', ', $grupos);
                        ?>
                        {{ $grupos }}
                    </td>
                    <td>
                        @if($ldapusers->getResults()[$i]->useraccountcontrol[0] == 512)
                          <form action="/ldapusers/{{ $ldapusers->getResults()[$i]->samaccountname[0] }}" method="post">
                            {{csrf_field()}} 
                            {{ method_field('patch') }}
                            <input type="hidden" name="status" value="disable">
                            <button class="btn btn-warning btn-sm" type="submit">Desativar</button>
                          </form>
                        @else
                          <form action="/ldapusers/{{ $ldapusers->getResults()[$i]->samaccountname[0] }}" method="post">
                            {{csrf_field()}} 
                            {{ method_field('patch') }}
                            <input type="hidden" name="status" value="enable">
                            <button class="btn btn-info btn-sm" type="submit">Ativar</button>
                          </form>
                        @endif
                    </td>
                    <td>
                        <form action="/ldapusers/{{ $ldapusers->getResults()[$i]->samaccountname[0] }}" method="post">
                          {{csrf_field()}} {{ method_field('delete') }}
                          <button class="delete-item btn btn-danger btn-sm" type="submit">Deletar</button>
                      </form>
                    </td>
                </tr>
                @endfor
            </tbody>
        </table>
    </div>
    {{-- Paginação --}}
    @include('ldapusers.nav')
</div>

@endsection

