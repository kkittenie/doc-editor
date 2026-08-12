<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showSignin()
    {
        if (Auth::check()) {
            return redirect()->route('document.index');
        }
        return view('pages.auth.signin', ['title' => 'Masuk ke Studio Papercraft']);
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Email atau Username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $loginInput = $request->input('login');
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $fieldType => $loginInput,
            'password' => $request->input('password'),
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('editor'));
        }

        return back()->withErrors([
            'login' => 'Email/Username atau kata sandi yang dimasukkan salah.',
        ])->onlyInput('login');
    }

    public function showSignup()
    {
        if (Auth::check()) {
            return redirect()->route('editor');
        }
        return view('pages.auth.signup', ['title' => 'Pendaftaran Penandatangan Resmi']);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'fname'     => ['required', 'string', 'max:255'],
            'lname'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8'],
        ]);

        $user = \App\Models\User::create([
            'name'     => $data['fname'].' '.$data['lname'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);

        return redirect()->route('editor')->with('success', 'Akun berhasil dibuat!');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('signin')->with('logged_out', '1');
    }
}