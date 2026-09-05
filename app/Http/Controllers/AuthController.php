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
            return redirect()->route(Auth::user()->homeRouteName());
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

            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Daftar path yang wajib role admin. Kalau URL "intended" si user
            // menunjuk ke salah satunya padahal dia bukan admin, kita abaikan
            // intended-nya dan arahkan ke halaman utama sesuai role-nya.
            // (Menghindari 403 "does not have the right roles" setelah login.)
            $adminOnlyPaths = ['/', '/dashboard', '/settings'];

            $intended = $request->session()->get('url.intended');
            $intendedPath = $intended !== null
                ? (parse_url($intended, PHP_URL_PATH) ?: '/')
                : null;

            $isAdminOnly = $intendedPath !== null
                && in_array($intendedPath, $adminOnlyPaths, true);

            if ($isAdminOnly && !$user->hasRole('admin')) {
                $request->session()->forget('url.intended');

                return redirect()->route($user->homeRouteName());
            }

            return redirect()->intended(route($user->homeRouteName()));
        }

        return back()->withErrors([
            'login' => 'Email/Username atau kata sandi yang dimasukkan salah.',
        ])->onlyInput('login');
    }

    public function showSignup()
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->homeRouteName());
        }
        return view('pages.auth.signup', ['title' => 'Pendaftaran Akses Digital Editor']);
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

        $user->assignRole('admin');

        Auth::login($user);

        return redirect()->route($user->homeRouteName())->with('success', 'Akun berhasil dibuat!');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('signin')->with('logged_out', '1');
    }
}