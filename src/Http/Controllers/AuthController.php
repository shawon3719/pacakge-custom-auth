<?php

namespace sws\smartauth\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use sws\smartauth\Http\Requests\ResetPasswordRequest;
use sws\smartauth\Http\Requests\StoreUserRequest;
use sws\smartauth\Services\AuthService;
use sws\smartauth\Models\Auth;

class AuthController extends Controller
{

    protected $authService;

    public function __construct()
    {
        $this->authService = new AuthService(); 
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function index()
    {
        return view('smartauth::Auth.register');
    }

    public function loginIndex()
    {
        return view('smartauth::Auth.login');
    }

    public function postLogin(Request $request)
    {
        $request->validate([
            'email'=>'required|email',
            'password'=>'required|min:8'
       ]);

        if(Auth::loginAttempt($request)){
            return redirect('/');
        }else{
            return redirect()->back();
        };

    }

    public function logout(){
        if(session()->has('LoggedUser')){
            session()->pull('LoggedUser');
            return redirect()->route('auth.login.index');
        }
    }

    public function register(StoreUserRequest $request)
    {
        
        $this->authService->register($request);

        return redirect()->route('auth.login.index');
    }


    public function verifyEmail($token)
    {
        $data = $this->authService->verifyEmail($token);
  
        return redirect()->route('auth.login.index')->with($data['type'], $data['message']);
    }


    public function forgotPassword(){
        return view('smartauth::Auth.passwords.forgot');
    }

    public function postForgotPassword(Request $request){

        $this->authService->forgotPassword($request);
        
        return redirect()->route('auth.forgot.password');
    }

    public function resetPassword($token){

        $data['token'] = $token;

        $data['email'] = Crypt::decrypt($token);

        $email_exist = Auth::where('email', $data['email'])->first();

        if($email_exist){
            return view('smartauth::Auth.passwords.reset', $data);
        }else{
            return redirect()->route('auth.login.index')->with('failed', 'We did not found your email in our system.');
        }
    }

    public function postResetPassword(ResetPasswordRequest $request){

        $password_reset = $this->authService->resetPassword($request);

        if($password_reset){
            return redirect()->route('auth.login.index');
        }else{
            return redirect()->back();
        }
        
    }
}