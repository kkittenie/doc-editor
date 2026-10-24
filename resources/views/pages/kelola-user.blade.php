@extends('layouts.app')

@section('content')

@php
    $flashSuccess = session('success');
    $flashError = session('error');

    $totalUsers = $users->count();
    $adminCount = $users->filter(fn ($u) => $u->hasRole('admin'))->count();

    // Data untuk tabel (dirender via Alpine agar bisa di-search client-side).
    $userData = $users->map(function ($user) {
        return [
            'id'       => $user->id,
            'name'     => $user->name,
            'username' => $user->username,
            'email'    => $user->email,
            'role'     => $user->getRoleNames()->first() ?? 'â€”',
            'initial'  => strtoupper(substr($user->name, 0, 1)),
            'is_self'  => $user->id === auth()->id(),
        ];
    })->values();

    $roles = $roles ?? ['admin'];
@endphp

<script>
    window.userPageData = @json($userData);
    window.userHasErrors = @json($errors->any());
</script>

<div class="container mx-auto py-8"
    x-data="{
        search: '',
        showCreate: window.userHasErrors,
        editing: null,
        users: window.userPageData,

        get filteredUsers() {
            const s = this.search.toLowerCase();
            if (!s) return this.users;
            return this.users.filter((u) =>
                (u.name || '').toLowerCase().includes(s) ||
                (u.email || '').toLowerCase().includes(s) ||
                (u.username || '').toLowerCase().includes(s)
            );
        },

        openCreate() { this.showCreate = true; },
        closeCreate() { this.showCreate = false; },
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
        closeEdit() { this.editing = null; },
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

    {{-- TITLE + AKSI --}}
    <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="font-serif text-2xl font-bold text-ink-900 dark:text-parchment-50">Kelola User</h1>
        </div>
        <button type="button" @click="openCreate()" class="btn-primary shrink-0 text-xs">
            + Tambah User
        </button>
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

    {{-- SUMMARY CARD (samakan dengan kartu S.O.F) --}}
    <div class="mb-6">
        <div class="w-40 rounded-xl border border-parchment-300 bg-white p-4 text-center dark:border-slate-warm-800 dark:bg-slate-warm-900">
            <p class="text-2xl font-bold text-ink-900 dark:text-parchment-50">{{ $totalUsers }}</p>
            <p class="text-[10px] uppercase text-slate-warm-500 dark:text-parchment-400">Jumlah User</p>
        </div>
    </div>


    {{-- TABLE USER --}}
    <div class="overflow-hidden rounded-2xl border border-parchment-300 bg-white shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900">

        {{-- Table Header --}}
        <div class="flex flex-col items-start justify-between gap-3 border-b border-parchment-200 px-5 py-4 dark:border-slate-warm-800 sm:flex-row sm:items-center">

            <div>
                <h2 class="text-sm font-semibold text-ink-900 dark:text-parchment-50">Daftar User</h2>
            </div>

            <div class="flex w-full items-center gap-2 sm:w-auto">
                <span class="rounded-full bg-parchment-100 px-2.5 py-1 text-[11px] font-medium text-slate-warm-600 dark:bg-slate-warm-800 dark:text-parchment-300"
                    x-text="filteredUsers.length + ' user'"></span>
                <div class="relative w-full sm:w-64">
                    <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-warm-400"
                        width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input type="text" x-model="search" placeholder="Cari nama / email / username..."
                        class="h-10 w-full rounded-xl border border-parchment-300 bg-parchment-25 pl-9 pr-3 text-xs outline-none transition placeholder:text-slate-warm-400 focus:border-crimson-600 focus:ring-2 focus:ring-crimson-600/10 dark:border-slate-warm-700 dark:bg-slate-warm-800 dark:text-parchment-100 dark:focus:border-crimson-500">
                </div>
            </div>

        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left">
                <thead class="bg-parchment-50 dark:bg-slate-warm-800/50">
                    <tr>
                        <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-warm-500 dark:text-parchment-400">Nama</th>
                        <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-warm-500 dark:text-parchment-400">Email</th>
                        <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-warm-500 dark:text-parchment-400">Role</th>
                        <th class="px-5 py-3 text-right text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-warm-500 dark:text-parchment-400">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="user in filteredUsers" :key="user.id">

                        <tr class="group border-t border-parchment-200 transition hover:bg-crimson-25 dark:border-slate-warm-800 dark:hover:bg-crimson-900/10">

                            {{-- Nama --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-crimson-100 bg-crimson-50 text-sm font-bold text-crimson-700 dark:border-crimson-900/40 dark:bg-crimson-900/20 dark:text-crimson-300"
                                        x-text="user.initial"></div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <p class="max-w-[220px] truncate text-sm font-semibold text-ink-900 dark:text-parchment-100" x-text="user.name"></p>
                                            <template x-if="user.is_self">
                                                <span class="text-[9px] font-bold text-slate-warm-400">(Anda)</span>
                                            </template>
                                        </div>
                                        <p class="mt-0.5 text-[11px] text-slate-warm-500 dark:text-parchment-400">
                                            <span x-show="user.username" x-text="user.username"></span>
                                            <span x-show="!user.username" class="italic">tanpa username</span>
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- Email --}}
                            <td class="px-5 py-4">
                                <span class="text-sm text-slate-warm-600 dark:text-parchment-300" x-text="user.email"></span>
                            </td>

                            {{-- Role --}}
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-full bg-ink-900 px-2.5 py-0.5 text-[10px] font-bold uppercase text-white dark:bg-bronze-500 dark:text-ink-900" x-text="user.role"></span>
                            </td>


                            {{-- Aksi --}}
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-1.5">
                                    <button type="button" @click="openEdit(user)"
                                        class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-parchment-300 px-2.5 text-[11px] font-semibold text-ink-900 transition hover:border-ink-900 hover:bg-ink-900 hover:text-white dark:border-slate-warm-700 dark:text-parchment-200 dark:hover:border-bronze-500 dark:hover:bg-bronze-500 dark:hover:text-ink-900"
                                        title="Edit user">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 20h9" />
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                        </svg>
                                        Edit
                                    </button>

                                    <template x-if="!user.is_self">
                                        <button type="button" @click="confirmDelete(user)"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-red-300 bg-white px-2.5 text-[11px] font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-500/60 dark:bg-transparent dark:text-red-400 dark:hover:bg-red-500/10"
                                            title="Hapus user">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6" />
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                                <path d="M10 11v6" />
                                                <path d="M14 11v6" />
                                                <path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" />
                                            </svg>
                                            Hapus
                                        </button>
                                    </template>
                                </div>
                            </td>

                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Empty State --}}
        <div x-show="filteredUsers.length === 0" x-cloak
            class="border-t border-parchment-200 px-6 py-14 text-center dark:border-slate-warm-800">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-parchment-100 text-slate-warm-400 dark:bg-slate-warm-800">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m20 20-4-4" />
                </svg>
            </div>
            <h3 class="mt-4 text-sm font-semibold text-ink-900 dark:text-parchment-50">User tidak ditemukan</h3>
            <p class="mt-1 text-xs text-slate-warm-500 dark:text-parchment-400">Coba ubah kata pencarian atau tambahkan user baru.</p>
        </div>

    </div>

    {{-- Form hapus tersembunyi (dikirim lewat SweetAlert) --}}
    <div class="hidden">
        @foreach ($users as $user)
            <form id="delete-form-{{ $user->id }}" action="{{ route('users.destroy', $user) }}" method="POST">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    </div>


    {{-- MODAL TAMBAH USER --}}
    <div x-show="showCreate" x-cloak x-transition.opacity
        class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-warm-900/60 p-4"
        @keydown.escape.window="closeCreate()">
        <div x-show="showCreate" x-transition
            class="w-full max-w-xl rounded-2xl border border-parchment-300 bg-white p-6 shadow-lg dark:border-slate-warm-700 dark:bg-slate-warm-900"
            @click.outside="closeCreate()">

            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="font-serif font-bold text-lg text-ink-900 dark:text-parchment-50">Tambah User</h3>
                </div>
                <button type="button" @click="closeCreate()" class="text-slate-warm-400 hover:text-ink-900 dark:hover:text-parchment-100">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300">
                    <strong class="mb-1 block">Periksa kembali form berikut:</strong>
                    <ul class="list-disc space-y-0.5 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('users.store') }}" method="POST" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @csrf

                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required value="{{ old('name') }}"
                        placeholder="Contoh: Budi Santoso"
                        class="w-full rounded-xl border border-parchment-300 bg-white px-3.5 py-2 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                </div>

                <div>
                    <label class="block text-xs font-medium mb-1.5">Username <span class="text-slate-warm-400">(opsional)</span></label>
                    <input type="text" name="username" value="{{ old('username') }}"
                        placeholder="mis. budi" autocomplete="off"
                        class="w-full rounded-xl border border-parchment-300 bg-white px-3.5 py-2 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                </div>

                <div>
                    <label class="block text-xs font-medium mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required value="{{ old('email') }}"
                        placeholder="nama@perusahaan.com"
                        class="w-full rounded-xl border border-parchment-300 bg-white px-3.5 py-2 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                </div>

                <div>
                    <label class="block text-xs font-medium mb-1.5">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required minlength="8" autocomplete="new-password"
                        placeholder="Minimal 8 karakter"
                        class="w-full rounded-xl border border-parchment-300 bg-white px-3.5 py-2 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                </div>

                <div>
                    <label class="block text-xs font-medium mb-1.5">Role <span class="text-red-500">*</span></label>
                    <select name="role" required
                        class="w-full rounded-xl border border-parchment-300 bg-white px-3.5 py-2 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                        <option value="" disabled selected>â€” Pilih role â€”</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2 pt-1 sm:col-span-2">
                    <button type="button" @click="closeCreate()"
                        class="flex-1 rounded-xl border border-parchment-300 px-4 py-2.5 text-sm font-semibold text-ink-900 transition hover:bg-parchment-100 dark:border-slate-warm-700 dark:text-parchment-200 dark:hover:bg-slate-warm-800">
                        Batal
                    </button>
                    <button type="submit" class="btn-primary flex-1 py-2.5 text-sm">Buat Akun</button>
                </div>
            </form>
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
                            @foreach ($roles as $role)
                                <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                            @endforeach
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

