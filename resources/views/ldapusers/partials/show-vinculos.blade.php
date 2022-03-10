@forelse ($vinculos as $vinculo)

  @switch($vinculo['tipvin'])
  
    @case('ALUNOPOS')
      <h4>Vínculo: {{ $vinculo['tipvin'] }}</h4>
      <table class="table table-sm table-striped ml-2">
        <tr>
          <td>Orientador</td>
          <td>{{ $vinculo['nompesori'] }}</td>
        </tr>
        <tr>
          <td>Programa</td>
          <td>{{ $vinculo['nomcur'] }} - nível {{ $vinculo['nivpgm'] }} </td>
        </tr>
        <tr>
          <td>Situação</td>
          <td>{{ $vinculo['sitoco'] }}</td>
        </tr>
        <tr>
          <td>Ingresso</td>
          <td>{{ $vinculo['dtainivin'] }} </td>
        </tr>
      </table>
    @break

    @case('SERVIDOR')
      <h4>Vínculo: {{ $vinculo['tipvin'] }}</h4>
      <table class="table table-sm table-striped ml-2">
        <tr>
          <td>Início</td>
          <td>{{ $vinculo['dtainivin'] }}</td>
        </tr>
        <tr>
          <td>Função</td>
          <td>{{ $vinculo['tipfnc'] }} - {{ $vinculo['nomabvfnc'] }}
            - {{ $vinculo['tipcon'] }} - {{ $vinculo['tipjor'] }} </td>
        </tr>
      </table>
    @break

  @endswitch
  @empty
    @if ($attr['codpes'])
      Sem vínculo ativo
    @else
      Usuário sem número USP
    @endif
  @endforelse
