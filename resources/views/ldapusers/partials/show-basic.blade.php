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
      <td>{{ $attr['grupos'] ?? '' }}</td>
    </tr>
    <tr>
      <td> <b> Descrição </b> </td>
      <td>{{ $attr['description'] ?? '' }}</td>
    </tr>
    <tr>
      <td><b>Nro. USP</b></td>
      <td>{{ $attr['codpes'] ?? '' }}</td>
    </tr>
    <tr>
      <td> <b> Departamento </b> </td>
      <td>{{ $attr['department'] ?? '' }}</td>
    </tr>
    <tr>
      <td> <b> Conta criada em </b> </td>
      <td>{{ $attr['ativacao'] ?? '' }}</td>
    </tr>
    <tr>
      <td> <b> Data da última alteração da senha </b> </td>
      <td> {{ $attr['senha_alterada_em'] ?? '' }} </td>
    </tr>
  </tbody>
</table>
