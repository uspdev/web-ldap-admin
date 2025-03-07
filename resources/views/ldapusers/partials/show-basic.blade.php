<table class="table table-striped">
  <tbody>

    <tr>
      <td style="width:30%"> <b>Seu nome </b> </td>
      <td>{{ $attr['display_name'] ?? '' }}</td>
    </tr>
    <tr>
      <td> <b> Email</b> </td>
      <td>{{ $attr['email'] ?? '' }}</td>
    </tr>
    <tr>
      <td> <b> Grupos </b> </td>
      <td>
        {{ $attr['grupos'] ?? '' }}
        @can('gerente')
        <button type="button" class="btn btn-success btn-sm ml-2" data-toggle="modal" data-target="#addGroup" data-backdrop="static"
          data-whatever="{{ $attr['username'] }} {{ $attr['display_name'] }}" data-keyboard="false" title="Adicionar ao(s) grupo(s)">
          <i class="fas fa-users" aria-hidden="true"></i> Grupo
        </button>
        @endcan
      </td>
    </tr>
    @if ($attr['description'])
      <tr>
        <td> <b> Descrição </b> </td>
        <td>{{ $attr['description'] }}</td>
      </tr>
    @endif
    <tr>
      <td><b>Nro. USP</b></td>
      <td>{{ $attr['codpes'] ?? '' }}</td>
    </tr>
    @if ($attr['department'])
      <tr>
        <td> <b> Departamento </b> </td>
        <td>{{ $attr['department'] }}</td>
      </tr>
    @endif
    <tr>
      <td> <b> Conta criada em </b> </td>
      <td>{{ $attr['ativacao'] ?? '' }}</td>
    </tr>
    <tr>
      <td> <b> Data da última alteração da senha </b> </td>
      <td> {{ $attr['senha_alterada_em'] ?? 'Usuário deve alterar senha no próximo logon' }} </td>
    </tr>
  </tbody>
</table>

@include('ldapusers.partials.add-group-form')