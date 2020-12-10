<?php

use App\Http\Controllers\indexController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\LdapUserController;
use App\Http\Controllers\ConfigController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [indexController::class, 'index']);

Route::get('login', [LoginController::class, 'redirectToProvider'])->name('login');
Route::get('callback', [LoginController::class, 'handleProviderCallback']);
Route::post('logout', [LoginController::class, 'logout']);

# temp
Route::get('logout', [LoginController::class, 'logout']);

# ldapusers
Route::post('ldapusers', [LdapUserController::class, 'store']);
Route::get('ldapusers', [LdapUserController::class, 'index']);
Route::get('ldapusers/solicita-admin', [LdapUserController::class, 'solicitaAdminForm']);
Route::post('ldapusers/solicita-admin', [LdapUserController::class, 'solicitaAdmin']);
Route::get('ldapusers/my', [LdapUserController::class, 'my']);
Route::get('ldapusers/create', [LdapUserController::class, 'create']);
Route::get('ldapusers/sync', [LdapUserController::class, 'syncReplicadoForm']);
Route::post('ldapusers/sync', [LdapUserController::class, 'syncReplicado']);
Route::patch('ldapusers/{username}', [LdapUserController::class, 'update']);
Route::get('ldapusers/{username}', [LdapUserController::class, 'show']);
Route::delete('ldapusers/{username}', [LdapUserController::class, 'destroy']);

#configs
Route::get('configs', [ConfigController::class, 'show']);
Route::post('configs', [ConfigController::class, 'update']);
