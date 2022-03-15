<table class="table table-striped">
  <tbody>
    @if (config('web-ldap-admin.mostrarFoto') == 1 && $foto != '')
      <tr>
        <td style="width:30%"> <b>Foto cartão USP </b> </td>
        <td><img style="width: 80px; float: left;" src="data:image/png;base64, {{ $foto }}" alt="foto"></td>
      </tr>
    @endif
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
      <td>{{ $attr['grupos'] ?? '' }}</td>
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
