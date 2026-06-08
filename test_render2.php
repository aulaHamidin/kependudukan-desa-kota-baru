<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Set a fake request so SessionGuard works
$request = Illuminate\Http\Request::create('/events/kelahiran/create', 'GET');
$app->instance('request', $request);
$kernel->bootstrap();

// Clear view cache
Illuminate\Support\Facades\Artisan::call('view:clear');
echo "View cache cleared." . PHP_EOL;

// Login as the first user
$user = App\Models\User::first();
if (!$user) {
    echo "No users found!" . PHP_EOL;
    exit(1);
}
echo "Logged in as: " . $user->email . PHP_EOL;
Illuminate\Support\Facades\Auth::login($user);

// Try rendering
app('view')->share('errors', new Illuminate\Support\ViewErrorBag());
echo "About to render..." . PHP_EOL;

// Catch fatal errors
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_COMPILE_ERROR])) {
        echo PHP_EOL . "=== FATAL ERROR ===" . PHP_EOL;
        echo "Type: " . $error['type'] . PHP_EOL;
        echo "Message: " . $error['message'] . PHP_EOL;
        echo "File: " . $error['file'] . PHP_EOL;
        echo "Line: " . $error['line'] . PHP_EOL;
    }
});

try {
    $view = view('data_peristiwa.kelahiran.create', [
        'rtOptions' => [],
        'agamaOptions' => [],
    ]);

    $html = $view->render();
    echo "SUCCESS! Rendered " . strlen($html) . " bytes." . PHP_EOL;
} catch (Throwable $e) {
    echo "FAILED!" . PHP_EOL;
    echo "Class: " . get_class($e) . PHP_EOL;
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
    echo PHP_EOL . "Trace:" . PHP_EOL;

    // Show relevant trace lines
    foreach ($e->getTrace() as $i => $frame) {
        $file = $frame['file'] ?? '(unknown)';
        $line = $frame['line'] ?? '?';
        if (str_contains($file, 'views') || str_contains($file, 'View') || str_contains($file, 'Blade') || $i < 5) {
            echo "  #$i $file:$line" . PHP_EOL;
        }
    }

    // Check previous exception
    $prev = $e->getPrevious();
    if ($prev) {
        echo PHP_EOL . "Caused by: " . get_class($prev) . PHP_EOL;
        echo "  Message: " . $prev->getMessage() . PHP_EOL;
        echo "  File: " . $prev->getFile() . ":" . $prev->getLine() . PHP_EOL;
    }
}
