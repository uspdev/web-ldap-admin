@section('styles')
  @parent
  <link rel="stylesheet" href="password_strength/password_strength_custom.css">
@endsection

<hr />
<h4>Alterar senha</h4>

<div class="row">
  <div class="col">
    <form method="POST" action="{{ url('/ldapusers/' . $attr['username']) }}">
      @csrf
      @method('patch')

      {{--
      // TODO: 01/07/2022 - ECAdev @alecosta: Parametrizar quais regras de complexidade devem ser verificadas
      // TODO: 01/07/2022 - ECAdev @alecosta: Validar as regras de complexidade também no servidor
      --}}

      {{-- https://github.com/mkurayan/password_strength --}}

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
  {{-- Por ora resolvi deixar a complexidade somente no /public/password_strength/password_strength_lightweight_custom.js  --}}
  {{-- <div class="col">
    <p style="color: red; font-size: 0.9rem;">
      @php
          $complexidade = explode(',', config('web-ldap-admin.senhaComplexidade'));
          foreach ($complexidade as $regra) {
              echo "$regra<br />";
          }
      @endphp
    </p>
  </div> --}}
</div>

@section('javascripts_bottom')
  @parent
	<script src="password_strength/password_strength_lightweight_custom.js"></script>
  <script type="text/javascript">
    $(document).ready(function() {
      $('#senha').strength_meter({
      //  CSS selectors
      strengthWrapperClass: 'input-group mb-3 w-50',
      inputClass: 'strength_input form-control',
      strengthMeterClass: 'strength_meter',
      toggleButtonClass: 'button_strength',
      // text for show / hide password links
      showPasswordText: '<i class="fas fa-eye" aria-hidden="true" title="Mostra senha"></i>',
      hidePasswordText: '<i class="fas fa-eye-slash" aria-hidden="true" title="Oculta senha"></i>'
      });
      $('#senha_confirmation').strength_meter({
      //  CSS selectors
      strengthWrapperClass: 'input-group mb-3 w-50',
      inputClass: 'strength_input form-control',
      strengthMeterClass: 'strength_meter',
      toggleButtonClass: 'button_strength',
      // text for show / hide password links
      showPasswordText: '<i class="fas fa-eye" aria-hidden="true" title="Mostra senha"></i>',
      hidePasswordText: '<i class="fas fa-eye-slash" aria-hidden="true" title="Oculta senha"></i>'
      });
      $('#senha').find("input[type=password]").each(function(ev) {
        if (!$(this).val()) {
          $(this).attr("placeholder", "Nova senha");
          $(this).attr("name", "senha");
        }
      });
      $('#senha_confirmation').find("input[type=password]").each(function(ev) {
        if (!$(this).val()) {
          $(this).attr("placeholder", "Confirma senha");
          $(this).attr("name", "senha_confirmation");
        }
      });
    })
  </script>
@endsection
