<?php

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

Route::get('/', 'indexController@index');

Route::get('login', 'Auth\LoginController@redirectToProvider')->name('login');
Route::get('login/senhaunica/callback', 'Auth\LoginController@handleProviderCallback');
Route::post('logout', 'Auth\LoginController@logout');

# Rotas para o usuário logado mudar atríbutos ldap
Route::get('/ldapusers', 'LdapUserController@show');
Route::post('/ldapusers', 'LdapUserController@mudaSenha');
