<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    const ROLES = ['admin', 'marketer'];

    public function index()
    {
        $users = User::with('roles')->orderBy('name')->get();

        return view('pages.kelola-user', [
            'title' => 'Kelola User',
            'users' => $users,
            'roles' => self::ROLES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            // Username bersifat opsional (boleh dikosongkan).
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['required', Rule::in(self::ROLES)],
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.unique'       => 'Email sudah digunakan.',
            'username.unique'    => 'Username sudah digunakan.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal 8 karakter.',
            'role.required'      => 'Pilih role (admin atau marketer).',
            'role.in'            => 'Role tidak valid.',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'username' => $data['username'] ?? null,
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->syncRoles([$data['role']]);

        return back()->with('success', "Akun '{$user->name}' berhasil dibuat sebagai {$data['role']}.");
    }

    public function update(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            $data = $request->validate([
                'name'     => ['required', 'string', 'max:255'],
                'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
                'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'role'     => ['required', Rule::in(['admin'])],
            ]);

            $user->fill([
                'name'     => $data['name'],
                'username' => $data['username'] ?? null,
                'email'    => $data['email'],
            ])->save();

            $user->syncRoles(['admin']);

            return back()->with('success', 'Profil Anda berhasil diperbarui.');
        }

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'     => ['required', Rule::in(self::ROLES)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->fill([
            'name'     => $data['name'],
            'username' => $data['username'] ?? null,
            'email'    => $data['email'],
        ]);

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
        $user->syncRoles([$data['role']]);

        return back()->with('success', "User '{$user->name}' berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return back()->with('success', "User '{$user->name}' berhasil dihapus.");
    }
}