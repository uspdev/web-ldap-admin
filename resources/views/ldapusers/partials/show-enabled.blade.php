<div class="btn-group">
  @if ($user->isEnabled())
    <button type="button" class="btn btn-sm btn-success">
      Conta habilitada <i class="fas fa-user ml-1"></i>
    </button>
  @else
    <button type="button" class="btn btn-sm btn-warning">
      Conta desabilitada <i class="fas fa-user-slash ml-1"></i>
    </button>
  @endif
  @can('gerente')
    <button type="button" class="btn btn-sm dropdown-toggle {{ $user->isEnabled() ? 'btn-success' : 'btn-warning' }}"
      id="dropdownMenuLink" data-toggle="dropdown"></button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
      <form action="ldapusers/{{ $user->samaccountname[0] }}" method="post">
        @csrf
        @method('patch')
        <input type="hidden" name="status" value="{{ $user->isEnabled() ? 'disable' : 'enable' }}">
        <button class="btn btn-light btn-sm" type="submit">
          {{ $user->isEnabled() ? 'Desabilitar' : 'Habilitar' }}
        </button>
      </form>
    </div>
  @endcan
</div>
