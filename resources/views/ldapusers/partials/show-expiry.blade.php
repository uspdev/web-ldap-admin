<div class="btn-group">
  @php
    $user_isExpired = \App\Ldap\User::getIsExpired($user);
    $user_expirationDate = \App\Ldap\User::getExpirationDate($user);
  @endphp
  <button class="btn btn-sm {{ $user_isExpired ? 'btn-warning' : 'btn-success' }}">
    @if ($user_expirationDate)
      {{ $user_isExpired ? 'Conta expirada em' : 'Conta válida até' }}
      <Strong>{{ $user_expirationDate->format('d/m/Y') }}</Strong>
    @else
      Conta não expira
    @endif
  </button>

  @if (Gate::check('manager'))
    <button type="button" class="btn btn-sm dropdown-toggle {{ $user_isExpired ? 'btn-warning' : 'btn-success' }}"
      id="dropdownMenuLink" data-toggle="dropdown"></button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
      <form action="ldapusers/{{ $user->samaccountname[0] }}" method="post">
        @csrf
        @method('patch')
        <button class="btn btn-sm dropdown-item" name="expiry" value="7" type="submit">
          Expirar daqui 1 semana
        </button>
        <button class="btn btn-sm dropdown-item" name="expiry" value="30" type="submit">
          Expirar daqui 1 mes
        </button>
        <button class="btn btn-sm dropdown-item" name="expiry" value="365" type="submit">
          Expirar daqui 1 ano
        </button>
        <div class="dropdown-divider"></div>
        <button class="btn btn-sm dropdown-item" name="expiry" value="0" type="submit">
          Não expirar
        </button>
        <button class="btn btn-sm dropdown-item" name="expiry" value="-1" type="submit"
          {{ $user_isExpired ? 'disabled' : '' }}>
          Expirar agora
        </button>
      </form>
    </div>
  @endif

</div>
