<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

/**
 * Pembersihan terakhir workflow marketer: role "marketer" sudah tidak
 * dikenali aplikasi (Kelola User hanya membuat admin, pendaftaran otomatis
 * admin, dan tidak ada lagi route/logika yang mengecek role marketer).
 *
 * Eks-marketer dinaikkan menjadi admin lebih dulu supaya tidak kehilangan
 * akses — semua aksi dokumen (simpan, ekspor, approval) mewajibkan
 * hasRole('admin'). Setelah itu role "marketer" dihapus; assignment user
 * ikut terbersih otomatis karena FK model_has_roles.role_id memakai
 * onDelete('cascade').
 */
return new class extends Migration
{
    public function up(): void
    {
        $marketer = Role::where('name', 'marketer')->first();

        if (! $marketer) {
            return;
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);

        foreach ($marketer->users()->get() as $user) {
            $user->assignRole($admin);
        }

        $marketer->delete();
    }

    public function down(): void
    {
        // Kembalikan role agar workflow marketer lama bisa di-restore
        // bersamaan dengan migrate:fresh + rollback migrasi workflow.
        Role::firstOrCreate(['name' => 'marketer']);
    }
};
