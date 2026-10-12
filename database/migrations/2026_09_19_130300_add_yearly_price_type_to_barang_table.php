<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE barang MODIFY price_type ENUM('one_time','daily','monthly','yearly') DEFAULT 'one_time'");
            return;
        }

        DB::transaction(function () {
            DB::statement('ALTER TABLE barang RENAME TO barang_legacy');
            $columns = DB::select('PRAGMA table_info(barang_legacy)');
            $defs = [];
            foreach ($columns as $column) {
                $defs[] = '"' . $column->name . '" ' . ($column->pk ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : DB::getSchemaBuilder()->getColumnType('barang_legacy', $column->name) . ($column->notnull ? ' NOT NULL' : '') . ($column->dflt_value !== null ? ' DEFAULT ' . $column->dflt_value : ''));
            }
            DB::statement('CREATE TABLE barang (' . implode(', ', $defs) . ')');
            DB::statement('INSERT INTO barang SELECT * FROM barang_legacy');
            DB::statement('DROP TABLE barang_legacy');
        });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE barang MODIFY price_type ENUM('one_time','daily','monthly') DEFAULT 'one_time'");
        }
    }
};
