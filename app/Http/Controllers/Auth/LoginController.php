<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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


    /**
    * Check either username or email.
    * @return string
    */
    public function username()
    {
       $identity  = request()->get('identity');
       $fieldName = filter_var($identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
       request()->merge([$fieldName => $identity]);
       return $fieldName;
    }

    /**
    * Validate the user login.
    * @param Request $request
    */
    protected function validateLogin(Request $request)
    {
       $this->validate(
           $request,
           [
               'identity' => 'required|string',
               'password' => 'required|string',
           ],
           [
               'identity.required' => 'Username or email is required',
               'password.required' => 'Password is required',
           ]
       );
    }

    /**
    * @param Request $request
    * @throws ValidationException
    */
    protected function sendFailedLoginResponse(Request $request)
    {
//       $request->session()->flash('error', trans('auth.failed'));
       throw ValidationException::withMessages([
           'error' => trans('auth.failed')
       ]);

//       return redirect('login');
    }

    public function authenticated(Request $request, $user)
    {
        $request->session()->flash('success', __('auth.success'));

        session()->put('last_login', Carbon::now()->toDateTimeString());
        session()->put('last_login_ip', $request->getClientIp());

        $user->save();
    }

    public function logout(Request $request)
    {

        Auth::user()->last_login = session('last_login', Carbon::now()->toDateTimeString());
        Auth::user()->last_login_ip = session('last_login_ip', $request->getClientIp());
        Auth::user()->save();

        $this->guard()->logout();
        $request->session()->invalidate();

        $request->session()->flash('success', __('auth.logout'));

        return $this->loggedOut($request) ?: redirect('/');
    }
}
