<div class="btn-group">
  <button class="btn btn-sm  {{ $user->isExpired() ? 'btn-warning' : 'btn-success' }}">
    @if ($user->expirationDate())
      {{ $user->isExpired() ? 'Senha expirada em' : 'Senha válida até' }}
      <Strong>{{ $user->expirationDate()->format('d/m/Y') }}</Strong>
    @else
      Senha não expira
    @endif
  </button>

  @if (Gate::check('gerente'))
    <button type="button" class="btn btn-sm dropdown-toggle {{ $user->isExpired() ? 'btn-warning' : 'btn-success' }}"
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
          {{ $user->isExpired() ? 'disabled' : '' }}>
          Expirar agora
        </button>
      </form>
    </div>
  @endif

</div>
