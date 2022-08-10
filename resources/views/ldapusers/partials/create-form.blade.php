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
    <label for="GRUPO">Grupo(s)</label>
    <select class="select2 form-control" id="grupos" name="grupos[]" multiple="multiple">
        @foreach (\App\Ldap\Group::listaGrupos() as $grupo)
            <option value="{{ $grupo }}">{{ $grupo }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
  <input type="submit" class="btn btn-primary" value="Enviar Dados">
</div>

@section('javascripts_bottom')
  @parent
  <script type="text/javascript">
    $(document).ready(function() {
        $("#grupos").select2 ({
            placeholder: "Selecione o(s) grupo(s) ou digite o(s) nome(s) de novo(s) grupo(s)",
            tags: true /* Aceita novas opções, ou seja, se o grupo não está na lista, será criado */
        });
    })
  </script>
@endsection
