<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Perluas tipe harga barang supaya mencakup opsi "Tahunan" (yearly).
 *
 * Alur kepemilikan & pembayaran:
 *  - dibeli     → harga ditetapkan, bertipe One Time.
 *  - disewa     → bisa dibayar perhari (daily), perbulan (monthly), ATAU pertahun (yearly).
 *  - dipinjamkan → barang tidak memiliki harga (kolom tetap ada, kosong boleh).
 *
 * MySQL: ubah enum price_type menjadi ['one_time','daily','monthly','yearly'].
 * SQLite: tidak ada tipe enum, kolom bertipe text — tidak perlu perubahan schema.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE barang MODIFY price_type ENUM('one_time','daily','monthly','yearly') DEFAULT 'one_time'");
            return;
        }

        // SQLite: enum Laravel membuat CHECK constraint lama; bangun ulang kolom price_type tanpa CHECK lama.
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
