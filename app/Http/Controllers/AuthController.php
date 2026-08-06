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
            return redirect()->route('editor');
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
<<<<<<< HEAD
        $data = $request->validate([
            'fname'     => ['required', 'string', 'max:255'],
            'lname'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8'],
        ]);

        $user = \App\Models\User::create([
            'name'     => $data['fname'],''.$data['lname'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
=======
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'max:255', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'first_name.required' => 'Nama depan wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, strip (-), dan garis bawah (_).',
            'username.unique' => 'Username ini sudah digunakan, silakan pilih username lain.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar. Silakan gunakan email lain atau masuk.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        $name = trim($request->first_name . ' ' . $request->last_name);

        $user = User::create([
            'name' => $name,
            'username' => strtolower($request->username),
            'email' => strtolower($request->email),
            'password' => Hash::make($request->password),
>>>>>>> b895488bd17b0375d2c9a5e1f97bb8bec3dcc502
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