@extends('layouts.app')

@section('content')

@php
    $flashSuccess = session('success');
    $flashError = session('error');
@endphp

<div class="max-w-6xl mx-auto py-8"
    x-data="{
        editing: null,
        openEdit(user) {
            this.editing = {
                id: user.id,
                name: user.name,
                username: user.username ?? '',
                email: user.email,
                role: user.role,
                isSelf: user.is_self
            };
        },
        closeEdit() {
            this.editing = null;
        },
        confirmDelete(user) {
            Swal.fire({
                icon: 'warning',
                title: 'Hapus user ' + user.name + '?',
                text: 'Aksi ini tidak bisa dibatalkan.',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc2626',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + user.id).submit();
                }
            });
        }
    }">

    {{-- TITLE --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-ink-900 dark:text-parchment-50">Kelola User</h1>
        <p class="mt-2 text-sm text-slate-warm-600 dark:text-parchment-400">
            Hanya admin yang dapat mengelola akun. Buat akun admin/marketer,
            ubah role, atau hapus user.
        </p>
    </div>

    @if ($flashSuccess)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: @json($flashSuccess), confirmButtonColor: '#1B2A4A' });
            });
        </script>
    @endif

    @if ($flashError)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({ icon: 'error', title: 'Terjadi Kesalahan', text: @json($flashError), confirmButtonColor: '#1B2A4A' });
            });
        </script>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300">
            <strong class="block mb-1">Periksa kembali form berikut:</strong>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-12 gap-6">

        {{-- FORM BUAT AKUN --}}
        <div class="col-span-12 lg:col-span-5">
            <div class="rounded-2xl border border-parchment-300 bg-white p-5 shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900">
                <h2 class="font-serif font-bold text-lg text-ink-900 dark:text-parchment-50 mb-1">Buat Akun Baru</h2>
                <p class="text-xs text-slate-warm-500 dark:text-parchment-400 mb-4">
                    Nama lengkap, email, dan password wajib. Username opsional.
                </p>

                <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-medium mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required value="{{ old('name') }}"
                            placeholder="Contoh: Budi Santoso"
                            class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1.5">Username <span class="text-slate-warm-400">(opsional)</span></label>
                        <input type="text" name="username" value="{{ old('username') }}"
                            placeholder="mis. budi" autocomplete="off"
                            class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1.5">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" required value="{{ old('email') }}"
                            placeholder="nama@perusahaan.com"
                            class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                    </div>
<div>
                        <label class="block text-xs font-medium mb-1.5">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required minlength="8" autocomplete="new-password"
                            placeholder="Minimal 8 karakter"
                            class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1.5">Role <span class="text-red-500">*</span></label>
                        <select name="role" required
                            class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                            <option value="" disabled selected>— Pilih role —</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="marketer" {{ old('role') === 'marketer' ? 'selected' : '' }}>Marketer</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary w-full py-2.5 text-sm">
                        Buat Akun
                    </button>
                </form>
            </div>
        </div>
{{-- DAFTAR USER --}}
        <div class="col-span-12 lg:col-span-7">
            <div class="rounded-2xl border border-parchment-300 bg-white shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900 overflow-hidden">
                <div class="px-5 py-4 border-b border-parchment-200 dark:border-slate-warm-800">
                    <h2 class="font-serif font-bold text-lg text-ink-900 dark:text-parchment-50">
                        Daftar User ({{ $users->count() }})
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-parchment-200 text-[11px] uppercase tracking-wide text-slate-warm-500 dark:border-slate-warm-800 dark:text-parchment-400">
                                <th class="px-5 py-3 font-semibold">Nama</th>
                                <th class="px-3 py-3 font-semibold">Email</th>
                                <th class="px-3 py-3 font-semibold">Role</th>
                                <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                @php
                                    $roleName = $user->getRoleNames()->first() ?? '—';
                                    $isSelf = $user->id === auth()->id();
                                @endphp
                                <tr class="border-b border-parchment-100 last:border-0 dark:border-slate-warm-800">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-ink-900 text-parchment-100 text-xs font-bold dark:bg-bronze-500 dark:text-ink-900">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-ink-900 dark:text-parchment-100">
                                                    {{ $user->name }}
                                                    @if ($isSelf)
                                                        <span class="ml-1 text-[9px] font-bold text-slate-warm-400">(Anda)</span>
                                                    @endif
                                                </p>
                                                <p class="text-xs text-slate-warm-500 dark:text-parchment-400">
                                                    @if ($user->username)
                                                        @{{ $user->username }}
                                                    @else
                                                        <span class="italic">tanpa username</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-slate-warm-600 dark:text-parchment-300">{{ $user->email }}</td>
                                    <td class="px-3 py-3">
                                        @if ($roleName === 'admin')
                                            <span class="inline-flex items-center rounded-full bg-ink-900 px-2.5 py-0.5 text-[10px] font-bold text-white dark:bg-bronze-500 dark:text-ink-900">Admin</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-bronze-100 px-2.5 py-0.5 text-[10px] font-bold text-bronze-800 dark:bg-bronze-900 dark:text-bronze-300">Marketer</span>
                                        @endif
                                    </td>
<td class="px-4 py-3 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            @php
                                                $editPayload = [
                                                    'id' => $user->id,
                                                    'name' => $user->name,
                                                    'username' => $user->username,
                                                    'email' => $user->email,
                                                    'role' => $roleName,
                                                    'is_self' => $isSelf,
                                                ];
                                                $deletePayload = ['id' => $user->id, 'name' => $user->name];
                                            @endphp
                                            <button type="button" @click="openEdit(@json($editPayload))"
                                                class="inline-flex h-8 items-center rounded-lg border border-parchment-300 px-2.5 text-[11px] font-semibold text-ink-900 transition hover:border-ink-900 hover:bg-ink-900 hover:text-white dark:border-slate-warm-700 dark:text-parchment-200 dark:hover:border-bronze-500 dark:hover:bg-bronze-500 dark:hover:text-ink-900">
                                                Edit
                                            </button>

                                            @if (!$isSelf)
                                                <form id="delete-form-{{ $user->id }}" action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button"
                                                        @click="confirmDelete(@json($deletePayload))"
                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-transparent text-slate-warm-400 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600 dark:hover:border-red-900/40 dark:hover:bg-red-900/20">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <polyline points="3 6 5 6 21 6"/>
                                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                                                            <path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-warm-500 dark:text-parchment-400">
                                        Belum ada user. Buat akun pertama melalui form di samping.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
{{-- MODAL EDIT --}}
    <div x-show="editing" x-cloak x-transition.opacity
        class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-warm-900/60 p-4"
        @keydown.escape.window="closeEdit()">
        <div x-show="editing" x-transition
            class="w-full max-w-md rounded-2xl border border-parchment-300 bg-white p-6 shadow-lg dark:border-slate-warm-700 dark:bg-slate-warm-900"
            @click.outside="closeEdit()">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="font-serif font-bold text-lg text-ink-900 dark:text-parchment-50">Edit User</h3>
                <button type="button" @click="closeEdit()" class="text-slate-warm-400 hover:text-ink-900 dark:hover:text-parchment-100">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <template x-if="editing">
                <form :action="`/users/${editing.id}`" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-medium mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="editing.name" required
                            class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1.5">Username <span class="text-slate-warm-400">(opsional)</span></label>
                        <input type="text" name="username" x-model="editing.username" autocomplete="off"
                            class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1.5">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" x-model="editing.email" required
                            class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1.5">Role <span class="text-red-500">*</span></label>
                        <select name="role" x-model="editing.role" required
                            class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                            <option value="admin">Admin</option>
                            <option value="marketer">Marketer</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1.5">Password Baru <span class="text-slate-warm-400">(kosongkan bila tidak diubah)</span></label>
                        <input type="password" name="password" minlength="8" autocomplete="new-password"
                            class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button type="button" @click="closeEdit()"
                            class="flex-1 rounded-xl border border-parchment-300 px-4 py-2.5 text-sm font-semibold text-ink-900 transition hover:bg-parchment-100 dark:border-slate-warm-700 dark:text-parchment-200 dark:hover:bg-slate-warm-800">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 btn-primary py-2.5 text-sm">Simpan</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection