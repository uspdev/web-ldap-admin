<?php

use App\Http\Controllers\IndexController;
use App\Http\Controllers\LdapUserController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\SolicitaController;

Route::get('/', [IndexController::class, 'index']);

# ldapusers
Route::post('ldapusers', [LdapUserController::class, 'store']);
Route::get('ldapusers', [LdapUserController::class, 'index']);
Route::get('ldapusers/my', [LdapUserController::class, 'my']);
Route::get('ldapusers/create', [LdapUserController::class, 'create']);
Route::get('ldapusers/sync', [LdapUserController::class, 'syncReplicadoForm']);
Route::post('ldapusers/sync', [LdapUserController::class, 'syncReplicado']);
Route::patch('ldapusers/{username}', [LdapUserController::class, 'update']);
Route::get('ldapusers/{username}', [LdapUserController::class, 'show']);
Route::delete('ldapusers/{username}', [LdapUserController::class, 'destroy']);
Route::post('ldapusers/group', [LdapUserController::class, 'addGroup']);

#configs
Route::get('configs', [ConfigController::class, 'show']);
Route::post('configs', [ConfigController::class, 'update']);

# Solicitação de conta de administração local do windows
Route::get('solicita', [SolicitaController::class, 'create']);
Route::post('solicita', [SolicitaController::class, 'store']);
