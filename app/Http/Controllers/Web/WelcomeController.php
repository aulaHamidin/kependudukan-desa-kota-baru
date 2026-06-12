<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\WelcomeStatsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function __construct(
        private readonly WelcomeStatsService $welcomeStatsService,
    ) {}

    public function __invoke(): View|RedirectResponse
    {
        // Jika user sudah login, redirect ke dashboard
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        try {
            $stats = $this->welcomeStatsService->getPublicStats();
        } catch (\Throwable $e) {
            Log::error('Welcome page data load failed', [
                'error' => $e->getMessage(),
            ]);

            $stats = $this->welcomeStatsService->emptyStats();
        }

        return view('welcome', $stats);
    }
}
