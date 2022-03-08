<div class="dropdown">
    <a class="btn btn-sm dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown">
        @if ($user->isEnabled())
            <i class="fas fa-user text-success"></i>
        @else
            <i class="fas fa-user-slash text-warning"></i>
        @endif
    </a>
    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
        <form action="ldapusers/{{ $user->samaccountname[0] }}" method="post">
            @csrf
            @method('patch')
            <input type="hidden" name="status" value="{{ $user->isEnabled() ? 'disable' : 'enable' }}">
            <button class="btn btn-light btn-sm" type="submit">
                {{ $user->isEnabled() ? 'Desabilitar' : 'Habilitar' }}
            </button>
        </form>
    </div>
</div>
