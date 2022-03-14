@forelse ($vinculos as $vinculo)
  <h5>Vínculo: {{ $vinculo['tipvinext'] }}</h5>
  <table class="table table-sm table-striped ml-2">
    <tr>
      <td>
        Início: {{ date_create($vinculo['dtainivin'])->format('d/m/Y') }}
        {{ $vinculo['nomfnc'] ? ' - Setor: ' . $vinculo['nomabvset'] : '' }}
        - Tel: {{ $vinculo['numtelfmt'] }}<br />
        {!! $vinculo['nomfnc'] ? 'Função: ' . $vinculo['nomfnc']. '<br />' : '' !!}
        Email: {{ $vinculo['codema'] }}
      </td>
    </tr>
    <tr>
      <td>
        @switch($vinculo['tipvinext'])
          @case('Aluno de Pós-Graduação')
            Orientador: {{ $vinculo['nompesori'] }},
            Programa: {{ $vinculo['nomcur'] }} - nível {{ $vinculo['nivpgm'] }}
          @break

          @case('Servidor')
            {{ $vinculo['tipcon'] }} - {{ $vinculo['tipjor'] }}
          @break

          @case('Docente')
          @break
        @endswitch
      </td>
    <tr>
  </table>
  @empty
    @if ($attr['codpes'])
      Sem vínculo ativo
    @else
      Usuário sem número USP
    @endif
  @endforelse
