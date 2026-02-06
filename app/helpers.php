<?php

use Carbon\Carbon;
use LdapRecord\Models\Model;

if (!function_exists('ldapToCarbon')) {
  /**
   * Converte um atributo do LDAP em Carbon, aceitando int, string ou objetos
   */
  function ldapToCarbon($user, string $attribute)
  {
    if (!$user instanceof Model)
      return null;

    $raw = $user->getFirstAttribute($attribute);

    // se já for um objeto de data (Carbon ou DateTime), retorna direto!
    if ($raw instanceof DateTimeInterface)
      return Carbon::instance($raw);

    // checagem dos valores "eternos" do Active Directory
    if (empty($raw) || $raw == 0 || $raw == '9223372036854775807')
      return null;

    try {
      // o asDateTime() faz a mágica de converter int para objeto
      return Carbon::instance($user->asDateTime($raw));
    } catch (\Exception $e) {
      return null;
    }
  }
}
