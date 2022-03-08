<span class="badge {{ $user->isExpired() ? 'badge-warning' : 'badge-success' }}">
    @if ($user->expirationDate())
        {{ $user->expirationDate()->format('d/m/Y') }}
    @else
        não expira
    @endif
</span>
