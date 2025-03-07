@if (session('password'))
  <hr />
  <h4 class="text-danger">Novo usuário</h4>

  <div class="ml-2">
    <div>
      Login: {{ $user->getAccountName() }}<br />
      Senha: {{ session('password') }}<br />
      Validade: {{ $user->expirationDate()->format('d/m/Y') }}
    </div>
    <div class="text-danger">
      Isso não será mostrado novamente!
    </div>
  </div>
@endif
