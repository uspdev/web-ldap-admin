<hr />
<h4>Alterar senha</h4>

<div class="row">
  <div class="col-sm-6 ml-2">
    <form method="POST" action="{{ url('/ldapusers/' . $attr['username']) }}">
      @csrf
      @method('patch')

      <span style="color: red;">
      @php
          $complexidade = explode(',', config('web-ldap-admin.senhaComplexidade'));
          foreach ($complexidade as $regra) {
              echo "$regra<br />";
          }
      @endphp
      </span>

      <div class="form-group">
        <label for="usr"> Nova senha:</label>
        <input type="password" class="form-control" name="senha" id="senha" placeholder="Digite a nova senha">
        <input type="checkbox" onclick="mostrarSenha('senha')"> Mostrar senha
      </div>

      <div class="form-group">
        <label for="usr"> Repetir Nova senha:</label>
        <input type="password" class="form-control" name="senha_confirmation" id="senha_confirmation" placeholder="Repita a nova senha">
        <input type="checkbox" onclick="mostrarSenha('senha_confirmation')"> Mostrar senha
      </div>

      @if (Gate::check('gerente'))
        <div class="form-group form-check">
          <input type="checkbox" class="form-check-input" name="must_change_pwd" value="1">
          <label for="usr">Usuário deve alterar a senha no próximo logon</label>
        </div>
      @endif

      <div class="form-group">
        <button type="submit" class="btn btn-success">Alterar</button>
      </div>
    </form>
  </div>
</div>

<script>
function mostrarSenha(field) {
  var x = document.getElementById(field);
  if (x.type === "password") {
    x.type = "text";
  } else {
    x.type = "password";
  }
}
</script>
