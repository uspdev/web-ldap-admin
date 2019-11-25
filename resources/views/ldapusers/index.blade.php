@extends('laravel-usp-theme::master')

@section('content')
@include('alerts')

<a href="/ldapusers/create" class="btn btn-success">Criar usuário não replicado</a>
<br><br>

<div class="panel panel-default">
    <div class="panel-heading"><strong>Filtros</strong></div>
        <div class="pt-2 pb-4">
            @php 
            // Informações para paginar
            $totPag = $ldapusers->getPages(); # Total de páginas
            $maxLnk = 5; # Máximo de links
            $pagCor = $ldapusers->getCurrentPage(); # Página atual
            $lnkLat = ceil($maxLnk / 2); # Cálculo dos links laterais
            $pagIni = $pagCor - $lnkLat; # Início dos links (esquerda)
            $pagFin = $pagCor + $lnkLat; # Fim dos links (direita)         
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
            <button type="button" class="btn btn-outline-dark" disabled>
                Grupos: <strong>{{ $searchGrupos }}</strong> 
            </button>
            <button type="button" class="btn btn-outline-dark" disabled>
                Busca: <strong>{{ $request->search }}</strong> 
            </button>      
            <button type="button" class="btn btn-outline-dark" disabled>
                Listando <strong>{{ $ldapusers->getPerPage() }}</strong> registros por página 
            </button> 
            <button type="button" class="btn btn-outline-dark" disabled>   
                Total de registros: <strong>{{ $ldapusers->count() }}</strong>
            </button>
        </div>
        <div class="panel-body">
        <form method="get" action="/ldapusers?search={{ $request->search }}{$gruposUrl}&page={{ $pagCor }}&perPage{{ $ldapusers->getPerPage() }}">
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
                <button type="submit" class="btn btn-success">Buscar</button>
            </form>
        </div>
</div>

<nav class="pt-3 pb-3" aria-label="...">
    <ul class="pagination justify-content-center">
        @php
            if ($pagCor == 1) {
                $priLnk = 'disabled';
            } else {
                $priLnk = '';
            }
            if ($pagCor == $totPag) {
                $ultLnk = 'disabled';
            } else {
                $ultLnk = '';
            }
        @endphp
        <li class="page-item {{ $priLnk }}"><a class="page-link" href="/ldapusers?page=1" aria-label="Previous"><span aria-hidden="true">Primeira</span></a></li>
        <li class="page-item {{ $priLnk }}"><a class="page-link" href="/ldapusers?page={{ ($pagCor - 1) }}" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>
        @for ($pag = $pagIni; $pag <= $pagFin; $pag++) 
            @if ($pag == $pagCor)
                <li class="page-item active"><a class="page-link" href="/ldapusers?page={{ $pag }}">{{ $pag }}</a></li>
            @else
                @if ($pag >= 1 and $pag <= $totPag)
                    <li class="page-item"><a class="page-link" href="/ldapusers?page={{ $pag }}">{{ $pag }}</a></li>
                @endif
            @endif 
        @endfor
        <li class="page-item {{ $ultLnk }}"><a class="page-link" href="/ldapusers?page={{ ($pagCor + 1) }}" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>
        <li class="page-item {{ $ultLnk }}"><a class="page-link" href="/ldapusers?page={{ $totPag }}" aria-label="Previous"><span aria-hidden="true">Última</span></a></li>
    </ul>
</nav>

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
            {{-- * * * FILTROS NÃO FUNCIONAM * * * --}}
            {{-- 
            usando 
            @foreach($ldapusers->getResults() as $ldapuser)
            FUNCIONA, porém a PAGINAÇÃO deixa de funcionar
            --}}
            @foreach($ldapusers as $ldapuser)
            <tr> 
                <td><a href="/ldapusers/{{$ldapuser->getAccountName()}}"> {{ $ldapuser->getAccountName() }}</a></td>
                <td> {{ $ldapuser->getDisplayName() }} </td>
                <td> {{ $ldapuser->getEmail() }} </td>
                <td><?php 
                        $grupos = array_diff($ldapuser->getGroupNames(),['Domain Users']);
                        $grupos = implode(', ', $grupos);
                    ?>
                    {{ $grupos }}
                </td>

                <td>
                    @if($ldapuser->useraccountcontrol[0] == 512)
                      <form action="/ldapusers/{{$ldapuser->samaccountname[0]}}" method="post">
                        {{csrf_field()}} 
                        {{ method_field('patch') }}
                        <input type="hidden" name="status" value="disable">
                        <button class="btn btn-warning btn-sm" type="submit">Desativar</button>
                      </form>
                    @else
                      <form action="/ldapusers/{{$ldapuser->samaccountname[0]}}" method="post">
                        {{csrf_field()}} 
                        {{ method_field('patch') }}
                        <input type="hidden" name="status" value="enable">
                        <button class="btn btn-info btn-sm" type="submit">Ativar</button>
                      </form>
                    @endif

                </td>
                
                <td>
                    <form action="/ldapusers/{{$ldapuser->samaccountname[0]}}" method="post">
                      {{csrf_field()}} {{ method_field('delete') }}
                      <button class="delete-item btn btn-danger btn-sm" type="submit">Deletar</button>
                  </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

@endsection

