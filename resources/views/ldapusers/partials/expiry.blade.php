<span class="badge {{ $user->isExpired() ? 'badge-warning' : 'badge-success' }}">
    @if ($user->expirationDate())
        @if (isset($label) && $label)
            {{ $user->isExpired() ? 'Expirado em' : 'Válido até' }}
        @endif
        {{ $user->expirationDate()->format('d/m/Y') }}
    @else
        Não expira
    @endif
</span>
