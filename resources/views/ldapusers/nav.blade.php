<nav id="paginacao" class="pt-3 pb-3" aria-label="...">
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
        <li class="page-item {{ $priLnk }}"><a class="page-link" 
            href="/ldapusers?page=1&perPage={{ $ldapusers->getPerPage() }}{{ $gruposUrl }}&search={{ $request->search }}" 
            aria-label="Previous"><span aria-hidden="true">Primeira</span></a></li>
        <li class="page-item {{ $priLnk }}"><a class="page-link" 
            href="/ldapusers?page={{ ($pagCor - 1) }}&perPage={{ $ldapusers->getPerPage() }}{{ $gruposUrl }}&search={{ $request->search }}" 
            aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>
        @for ($pag = $pagIni; $pag <= $pagFin; $pag++) 
            @if ($pag == $pagCor)
                <li class="page-item active"><a class="page-link" 
                    href="/ldapusers?page={{ $pag }}&perPage={{ $ldapusers->getPerPage() }}{{ $gruposUrl }}&search={{ $request->search }}">
                    {{ $pag }}</a></li>
            @else
                @if ($pag >= 1 and $pag <= $totPag)
                    <li class="page-item"><a class="page-link" 
                        href="/ldapusers?page={{ $pag }}&perPage={{ $ldapusers->getPerPage() }}{{ $gruposUrl }}&search={{ $request->search }}">
                        {{ $pag }}</a></li>
                @endif
            @endif 
        @endfor
        <li class="page-item {{ $ultLnk }}"><a class="page-link" 
            href="/ldapusers?page={{ ($pagCor + 1) }}&perPage={{ $ldapusers->getPerPage() }}{{ $gruposUrl }}&search={{ $request->search }}" 
            aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>
        <li class="page-item {{ $ultLnk }}"><a class="page-link" 
            href="/ldapusers?page={{ $totPag }}&perPage={{ $ldapusers->getPerPage() }}{{ $gruposUrl }}&search={{ $request->search }}" 
            aria-label="Previous"><span aria-hidden="true">Última</span></a></li>
    </ul>
</nav>