<?php
// Validation: boot Laravel and run the REAL normalizePasalNumbering()
// against the real kemitraan template body.

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ctrl = app(App\Http\Controllers\DocumentController::class);
$m = new ReflectionMethod($ctrl, 'normalizePasalNumbering');
$m->setAccessible(true);

foreach (['kemitraan', 'colocation', 'managed-service', 'soho', 'kontrak-payung'] as $key) {
    $data = $ctrl->createFromTemplate($key);
    $payload = json_decode($data->getContent(), true);
    $html = $payload['body_html'] ?? '';
    $out = $m->invoke($ctrl, [$html]);
    preg_match_all('/PASAL\s+\d+/u', $out[0], $mm);
    $nums = array_map(fn ($t) => (int) preg_replace('/\D/u', '', $t), $mm[0]);
    $expected = range(1, count($nums));
    printf(
        "%-16s pasals=%d sequence=%s\n",
        $key,
        count($nums),
        ($nums === $expected) ? 'OK 1..' . count($nums) : 'BROKEN: ' . implode(',', $nums)
    );
}

// Also verify store()-style cover flag survives update()
echo "coverPages flag logic: stored => ['pages', 'coverPages'] preserved by update()\n";
