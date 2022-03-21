<hr />
<h4>Alterar senha</h4>

<div class="row">
  <div class="col-sm-6 ml-2">
    <form method="POST" action="{{ url('/ldapusers/' . $attr['username']) }}">
      @csrf
      @method('patch')

      <div class="form-group">
        <label for="usr"> Nova senha:</label>
        <input type="password" class="form-control" name="senha">
        <i> Mínimo de 8 caracteres. </i>
      </div>

      <div class="form-group">
        <label for="usr"> Repetir Nova senha:</label>
        <input type="password" class="form-control" name="senha_confirmation">
      </div>

      @if (Gate::check('gerente') and config('web-ldap-admin.obrigaTrocarSenhaNoWindows'))
        <div class="form-group form-check">
          <input type="checkbox" class="form-check-input" name="must_change_pwd" value="1" checked>
          <label for="usr">Usuário deve alterar a senha no próximo logon</label>
        </div>
      @endif

      <div class="form-group">
        <button type="submit" class="btn btn-success">Alterar</button>
      </div>
    </form>
  </div>
</div>
