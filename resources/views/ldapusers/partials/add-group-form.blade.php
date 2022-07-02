<div class="modal fade" id="addGroup" tabindex="-1" role="dialog" aria-labelledby="addGroup" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addGroup">Adicionar usuário ao(s) grupo(s)</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <h5></h5>
            <form method="post" action="{{ url('/ldapusers/group') }}">
                {{ csrf_field() }}
                <div class="form-group">
                    <label for="grupos">Selecione o(s) grupo(s) ou digite o(s) nome(s) de novo(s) grupo(s)</label>
                    <select class="select2 form-control" id="grupos" name="grupos[]" multiple="multiple" required>
                        @foreach (\App\Ldap\Group::listaGrupos() as $grupo)
                            @if (!in_array($grupo, explode(', ', $attr['grupos'])))
                                <option value="{{ $grupo }}">{{ $grupo }}</option>
                            @endif
                        @endforeach
                    </select>
                    <input name="username" type="hidden" value="{{ $attr['username'] }}" />
                </div>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </form>
        </div>
      </div>
    </div>
</div>
