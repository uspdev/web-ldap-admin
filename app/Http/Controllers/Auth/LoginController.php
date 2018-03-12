<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Socialite;
use App\User;
use Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function redirectToProvider()
    {
        return Socialite::driver('senhaunica')
            ->redirect();
    }

    public function handleProviderCallback()
    {
        $user = Socialite::driver('senhaunica')->user();

        $authUser = User::where('codpes', $user->id)->first();
        if (!$authUser)
        {
            User::create([
                'name'     => $user->name,
                'email'    => $user->email,
                'codpes' => $user->id,
            ]);
        }
        Auth::login($authUser, true);
        return redirect('/');

    }

    public function logout(Request $request) {
      Auth::logout();
      return redirect('/');
    }

}
