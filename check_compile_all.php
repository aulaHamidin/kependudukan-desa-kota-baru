<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Clear view cache first
$viewPath = config('view.compiled');
foreach (glob("{$viewPath}/*") as $file) {
    if (is_file($file)) unlink($file);
}
echo "View cache cleared." . PHP_EOL;

// Now try to render the view with all its components
try {
    // We need to be authenticated to render sidebar etc
    // Instead, let's compile all blade files individually and check compiled PHP syntax

    $finder = app('view')->getFinder();
    $compiler = app('view')->getEngineResolver()->resolve('blade')->getCompiler();

    // Get all blade files
    $views_path = resource_path('views');
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($views_path)
    );

    $errors = [];
    foreach ($iterator as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
            $path = $file->getPathname();
            try {
                $compiler->compile($path);
                $compiledPath = $compiler->getCompiledPath($path);

                // PHP syntax check
                exec('php -l ' . escapeshellarg($compiledPath) . ' 2>&1', $output, $ret);
                if ($ret !== 0) {
                    $relPath = str_replace($views_path . DIRECTORY_SEPARATOR, '', $path);
                    $errors[$relPath] = implode(' ', $output);
                }
                $output = [];
            } catch (\Exception $e) {
                $relPath = str_replace($views_path . DIRECTORY_SEPARATOR, '', $path);
                $errors[$relPath] = $e->getMessage();
            }
        }
    }

    if (empty($errors)) {
        echo "All " . iterator_count(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($views_path))) . " blade files compile successfully!" . PHP_EOL;
    } else {
        echo "=== COMPILATION ERRORS ===" . PHP_EOL;
        foreach ($errors as $file => $error) {
            echo PHP_EOL . "FILE: $file" . PHP_EOL;
            echo "  ERROR: $error" . PHP_EOL;
        }
    }
} catch (\Exception $e) {
    echo "Fatal: " . $e->getMessage() . PHP_EOL;
}
