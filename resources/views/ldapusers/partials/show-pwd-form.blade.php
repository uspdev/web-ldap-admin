<hr />
<h4>Alterar senha</h4>

<div class="row">
  <div class="col-sm-4 ml-2">
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

      <div class="form-group">
        <button type="submit" class="btn btn-success">Alterar</button>
      </div>
    </form>
  </div>
</div>
