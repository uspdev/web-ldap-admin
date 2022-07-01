@section('styles')
  @parent
  <link rel="stylesheet" href="password_strength/password_strength_custom.css">
@endsection

<hr />
<h4>Alterar senha <i class="fas fa-bell text-info" aria-hidden="true"></i></h4>

<div class="row">
  <div class="col-sm-6 ml-2">
    <form method="POST" action="{{ url('/ldapusers/' . $attr['username']) }}">
      @csrf
      @method('patch')

      {{--
      // TODO: 01/07/2022 - ECAdev @alecosta: Popover com as regras de complexidade
      // TODO: 01/07/2022 - ECAdev @alecosta: Adicionar no plugin password strength as regras de complexidade que faltam
      // TODO: 01/07/2022 - ECAdev @alecosta: Parametrizar quais regras de complexidade devem ser verificadas
      // TODO: 01/07/2022 - ECAdev @alecosta: Adicionar um ícone de olho no campo input para mostrar ou ocultar a senhapas
      // TODO: 01/07/2022 - ECAdev @alecosta: Validar as regras de complexidade também no servidor
      --}}

      <div id="senha"></div>

      <div id="senha_confirmation"></div>

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

@section('javascripts_bottom')
  @parent
	<script src="password_strength/password_strength_lightweight_custom.js"></script>
  <script type="text/javascript">
    $(document).ready(function() {
      $('#senha').strength_meter({
      //  CSS selectors
      strengthWrapperClass: 'strength_wrapper',
      inputClass: 'strength_input form-control',
      strengthMeterClass: 'strength_meter',
      toggleButtonClass: 'button_strength',
      // text for show / hide password links
      showPasswordText: 'Mostrar senha',
      hidePasswordText: 'Ocultar senha'
      });
      $('#senha_confirmation').strength_meter({
      //  CSS selectors
      strengthWrapperClass: 'strength_wrapper',
      inputClass: 'strength_input form-control',
      strengthMeterClass: 'strength_meter',
      toggleButtonClass: 'button_strength',
      // text for show / hide password links
      showPasswordText: 'Mostrar senha',
      hidePasswordText: 'Ocultar senha'
      });
      $('#senha').find("input[type=password]").each(function(ev) {
        if (!$(this).val()) {
          $(this).attr("placeholder", "Nova senha");
        }
      });
      $('#senha_confirmation').find("input[type=password]").each(function(ev) {
        if (!$(this).val()) {
          $(this).attr("placeholder", "Confirma senha");
        }
      });
    })
  </script>
@endsection
