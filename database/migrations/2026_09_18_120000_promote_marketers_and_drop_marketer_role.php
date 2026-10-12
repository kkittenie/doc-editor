<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

/**
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
