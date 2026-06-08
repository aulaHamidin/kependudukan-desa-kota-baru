<?php
// Recursively check ALL blade files for directive balance
function checkFile($file)
{
    $content = file_get_contents($file);

    // Match all blade directives that need closing
    $pairs = [
        'if' => 'endif',
        'error' => 'enderror',
        'isset' => 'endisset',
        'unless' => 'endunless',
        'can' => 'endcan',
        'cannot' => 'endcannot',
        'foreach' => 'endforeach',
        'forelse' => 'endforelse',
        'for' => 'endfor',
        'while' => 'endwhile',
        'switch' => 'endswitch',
        'push' => 'endpush',
        'section' => 'endsection',
        'component' => 'endcomponent',
        'slot' => 'endslot',
        'verbatim' => 'endverbatim',
        'php' => 'endphp',
        'once' => 'endonce',
        'prepend' => 'endprepend',
        'empty' => null, // handled by forelse
        'auth' => 'endauth',
        'guest' => 'endguest',
    ];

    // hasSection acts like @if (needs @endif)
    $hasSection = preg_match_all('/@hasSection\s*\(/', $content);

    $issues = [];
    foreach ($pairs as $open => $close) {
        if ($close === null) continue;

        // Count openers
        $openPattern = '/@' . preg_quote($open) . '\s*[\(\s]/';
        $openCount = preg_match_all($openPattern, $content);

        // Count closers
        $closePattern = '/@' . preg_quote($close) . '(\s|$)/m';
        $closeCount = preg_match_all($closePattern, $content);

        if ($open === 'if') {
            $openCount += $hasSection; // hasSection needs endif
        }

        // Account for @else and @elseif (they don't open new blocks)

        if ($openCount !== $closeCount) {
            $issues[] = "@$open=$openCount vs @$close=$closeCount";
        }
    }

    return $issues;
}

$dir = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator('resources/views')
);

$problems = [];
foreach ($dir as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        $issues = checkFile($path);
        if (!empty($issues)) {
            $problems[$path] = $issues;
        }
    }
}

if (empty($problems)) {
    echo "All blade files have balanced directives!" . PHP_EOL;
} else {
    echo "=== FILES WITH ISSUES ===" . PHP_EOL;
    foreach ($problems as $file => $issues) {
        echo PHP_EOL . "FILE: $file" . PHP_EOL;
        foreach ($issues as $issue) {
            echo "  - $issue" . PHP_EOL;
        }
    }
}
