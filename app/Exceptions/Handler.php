<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;
use TypeError;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Handle TypeError from strict_types for user-friendly messages
        $this->renderable(function (TypeError $e, $request) {
            // Log detail error untuk developer
            Log::error('Type Error occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => auth()->id(),
                'input' => $request->except(['password', 'password_confirmation']),
            ]);

            // Return user-friendly response
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Terjadi kesalahan validasi data. Silakan periksa input Anda dan coba lagi.',
                    'error_code' => 'TYPE_ERROR',
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors([
                    'system' => 'Terjadi kesalahan pada sistem. Format data tidak sesuai. Silakan coba lagi.',
                ]);
        });
    }
}
