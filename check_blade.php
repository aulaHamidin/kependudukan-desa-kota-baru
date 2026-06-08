<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$path = resource_path('views/data_peristiwa/kelahiran/create.blade.php');
$compiler = app('view')->getEngineResolver()->resolve('blade')->getCompiler();
$compiler->compile($path);
$compiled = $compiler->getCompiledPath($path);

echo "Compiled path: $compiled" . PHP_EOL;

// Check syntax
exec('php -l ' . escapeshellarg($compiled), $out, $ret);
echo implode(PHP_EOL, $out) . PHP_EOL;

if ($ret !== 0) {
    // Show the problematic lines
    $lines = file($compiled);
    $total = count($lines);
    echo "Total compiled lines: $total" . PHP_EOL;

    // Find "if" and "endif" 
    $ifStack = [];
    foreach ($lines as $i => $line) {
        $lineNum = $i + 1;
        if (preg_match('/if\s*\(/', $line) && !preg_match('/endif/', $line)) {
            $ifStack[] = ['line' => $lineNum, 'code' => trim($line)];
        }
        if (preg_match('/endif/', $line)) {
            if (!empty($ifStack)) {
                array_pop($ifStack);
            }
        }
    }

    echo PHP_EOL . "=== Unclosed if statements ===" . PHP_EOL;
    foreach ($ifStack as $item) {
        echo "Line {$item['line']}: {$item['code']}" . PHP_EOL;
    }
}
