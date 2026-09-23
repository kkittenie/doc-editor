<?php
/**
 * Pulihkan body_content dokumen dari backup hasil _repair_doc_pasal.php --apply.
 *
 * Pakai : php _restore_doc_repair.php <id> [id ...]
 * Contoh: php _restore_doc_repair.php 180
 *
 * Backup diambil dari storage/app/repair-backup/doc-<id>.json
 */
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$ids = array_slice($argv, 1);
if (!$ids) {
    echo "Pakai: php _restore_doc_repair.php <id> [id ...]\n";
    exit(1);
}

foreach ($ids as $id) {
    if (!ctype_digit((string)$id)) {
        echo "lewati (id tidak numerik): $id\n";
        continue;
    }
    $file = __DIR__ . '/storage/app/repair-backup/doc-' . $id . '.json';
    if (!file_exists($file)) {
        echo "doc $id: backup TIDAK ADA ($file)\n";
        continue;
    }
    $orig = json_decode((string)file_get_contents($file), true);
    if (!is_array($orig) || !isset($orig['pages'])) {
        echo "doc $id: backup rusak\n";
        continue;
    }
    DB::table('documents')
        ->where('id', (int)$id)
        ->update([
            'body_content' => json_encode($orig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    echo "doc $id: DIPULIHKAN dari backup (" . count($orig['pages']) . " halaman)\n";
}
