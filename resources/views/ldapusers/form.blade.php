<div class="form-group">
    <label for="username">Username</label>
    <input type="text" class="form-control" name="username" value="{{ old('username') }}" required >
    <i>Somente letras e números</i>
</div>

<div class="form-group">
    <label for="nome">Nome</label>
    <input type="text" class="form-control" name="nome" value="{{ old('nome') }}" required >
</div>

<div class="form-group">
    <label for="email">Email</label>
    <input type="text" class="form-control" name="email" value="{{ old('email') }}" required >
</div>

<div class="form-group">
    <label for="GRUPO">Grupo</label>
    <input type="text" class="form-control" name="grupo" value="{{ old('grupo') }}" >
</div>

<div class="form-group">
  <input type="submit" class="btn btn-primary" value="Enviar Dados">
</div>
