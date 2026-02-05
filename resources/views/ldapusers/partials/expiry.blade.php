@php
    $user_isExpired = \App\Ldap\User::getIsExpired($user);
    $user_expirationDate = \App\Ldap\User::getExpirationDate($user);
@endphp
<span class="badge {{ $user_isExpired ? 'badge-warning' : 'badge-success' }}">
    @if ($user_expirationDate)
        {{ $user_expirationDate->format('d/m/Y') }}
    @else
        não expira
    @endif
</span>
