<nav id="paginacao" class="navigation mt-3" aria-label="...">
  <ul class="pagination justify-content-center">

    <li class="page-item disabled">
      <span class="page-link ">Total de registros: <strong>{{ $nav['total'] }}</strong></span>
    </li>
    <li lass="page-item">&nbsp; &nbsp;</li>

    <li class="page-item {{ $nav['pagCor'] == 1 ? 'disabled' : '' }}">
      <a class="page-link"
        href="ldapusers?page=1&perPage={{ $nav['perPag'] }}{{ $gruposUrl }}&search={{ $request->search }}"
        aria-label="Previous">
        <span aria-hidden="true">Primeira</span>
      </a>
    </li>
    <li class="page-item {{ $nav['pagCor'] == 1 ? 'disabled' : '' }}">
      <a class="page-link"
        href="ldapusers?page={{ $nav['pagCor'] - 1 }}&perPage={{ $nav['perPag'] }}{{ $gruposUrl }}&search={{ $request->search }}"
        aria-label="Previous">
        <span aria-hidden="true">&laquo;</span>
      </a>
    </li>

    @for ($pag = $nav['pagIni']; $pag <= $nav['pagFin']; $pag++)
      @if ($pag == $nav['pagCor'])
        <li class="page-item active">
          <a class="page-link"
            href="ldapusers?page={{ $pag }}&perPage={{ $nav['perPag'] }}{{ $gruposUrl }}&search={{ $request->search }}">
            {{ $pag }}
          </a>
        </li>
      @else
        @if ($pag >= 1 and $pag <= $nav['totPag'])
          <li class="page-item"><a class="page-link"
              href="ldapusers?page={{ $pag }}&perPage={{ $nav['perPag'] }}{{ $gruposUrl }}&search={{ $request->search }}">
              {{ $pag }}</a></li>
        @endif
      @endif
    @endfor

    <li class="page-item {{ $nav['pagCor'] == $nav['totPag'] ? 'disabled' : '' }}">
      <a class="page-link"
        href="ldapusers?page={{ $nav['pagCor'] + 1 }}&perPage={{ $nav['perPag'] }}{{ $gruposUrl }}&search={{ $request->search }}"
        aria-label="Next">
        <span aria-hidden="true">&raquo;</span>
      </a>
    </li>
    <li class="page-item {{ $nav['pagCor'] == $nav['totPag'] ? 'disabled' : '' }}">
      <a class="page-link"
        href="ldapusers?page={{ $nav['totPag'] }}&perPage={{ $nav['perPag'] }}{{ $gruposUrl }}&search={{ $request->search }}"
        aria-label="Previous">
        <span aria-hidden="true">Última</span>
      </a>
    </li>
  </ul>
</nav>
