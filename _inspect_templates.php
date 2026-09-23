<?php
require __DIR__.'/vendor/autoload.php';
$all = App\Data\ContractTemplates::all();
foreach ($all as $k => $v) {
    echo "=== $k ===\n";
    if (isset($v['body_content']['isi'])) {
        $i = 1;
        foreach ($v['body_content']['isi'] as $pasal) {
            echo $i++.'. '.($pasal['judul'] ?? '?')."\n";
        }
    } elseif (isset($v['isi'])) {
        $i = 1;
        foreach ($v['isi'] as $pasal) {
            echo $i++.'. '.($pasal['judul'] ?? json_encode(array_keys($pasal)))."\n";
        }
    } else {
        echo 'keys: '.implode(',', array_keys($v))."\n";
        if (isset($v['body_content'])) echo 'body_content keys: '.implode(',', array_keys($v['body_content']))."\n";
    }
    echo "\n";
}
