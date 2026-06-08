<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a fake request to the kelahiran create page
$request = Illuminate\Http\Request::create('/events/kelahiran/create', 'GET');

try {
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . PHP_EOL;
    if ($response->getStatusCode() >= 400) {
        $content = $response->getContent();
        // Extract error message
        if (preg_match('/<title>(.*?)<\/title>/s', $content, $m)) {
            echo "Title: " . trim($m[1]) . PHP_EOL;
        }
        // Look for the actual error
        if (preg_match('/class="exception-message"[^>]*>(.*?)<\//s', $content, $m)) {
            echo "Error: " . trim(strip_tags($m[1])) . PHP_EOL;
        }
    }
} catch (Throwable $e) {
    echo "Exception: " . get_class($e) . PHP_EOL;
    echo "Message: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;

    // Get the underlying cause
    $prev = $e->getPrevious();
    while ($prev) {
        echo PHP_EOL . "Caused by: " . get_class($prev) . PHP_EOL;
        echo "Message: " . $prev->getMessage() . PHP_EOL;
        echo "File: " . $prev->getFile() . ":" . $prev->getLine() . PHP_EOL;
        $prev = $prev->getPrevious();
    }
}

$kernel->terminate($request, $response ?? new Illuminate\Http\Response());
