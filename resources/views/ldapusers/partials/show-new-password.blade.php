@if (session('password'))
  <hr />
  <h4 class="text-danger">Novo usuário</h4>

  <div class="ml-2">
    <div>
      Login: {{ $user->getFirstAttribute('samaccountname') }}<br />
      Senha: {{ session('password') }}<br />
      @php
        $user_expirationDate = \App\Ldap\User::getExpirationDate($user);
      @endphp
      @if ($user_expirationDate)
        Validade: {{ $user_expirationDate->format('d/m/Y') }}
      @else
        Validade: Sem validade
      @endif
    </div>
    <div class="text-danger">
      Isso não será mostrado novamente!
    </div>
  </div>
@endif
