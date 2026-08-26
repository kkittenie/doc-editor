<?php
require __DIR__ . '/vendor/autoload.php';

$c = new Illuminate\View\Compilers\BladeCompiler(
    new Illuminate\Filesystem\Filesystem,
    sys_get_temp_dir()
);

$s = $c->compileString(
    file_get_contents(__DIR__ . '/resources/views/pages/editor.blade.php')
);

echo strlen($s) > 2000 ? 'BLADE_OK len=' . strlen($s) : 'BLADE_SUSPECT len=' . strlen($s);
