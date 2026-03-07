<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Requests\SignupRequest;
use App\Http\Requests\LoginRequest;




class AuthController extends Controller
{
    public function showsignup()
    {
        return view('auth.signup',['page_Title' => 'Signup']);

    }

 public function signup(SignupRequest $request)
    {
        $user = new User();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = ($request->input('password'));
        $user->save();

        Auth::login($user);

        return redirect('/');

    }

     public function showlogin()
    {
        return view('auth.login',['page_Title' => 'Login']);


    }

      public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        if(Auth::attempt($credentials)){
            $request->session()->regenerate();
            return redirect('/');
        }
        else
            {
            return back()->withErrors(['email' => 'Invalid email or password.']);
        }


    }

       public function logout()
    {
        Auth::logout();
          return redirect('/login');

    }

}
