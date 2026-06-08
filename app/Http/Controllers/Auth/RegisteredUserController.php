<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ViewerRegisterRequest;
use App\Providers\RouteServiceProvider;
use App\Services\ViewerRegistrationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * Controller for handling user registration functionality
 */
class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     * 
     * @return View Returns the registration form view
     */
    public function create(): View
    {
        // Return the registration form view
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     * Process user registration, fire events, and authenticate the user
     *
     * @param ViewerRegisterRequest $request Validated registration request
     * @param ViewerRegistrationService $service Service for handling registration logic
     * @return RedirectResponse Redirect to home page after successful registration
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(ViewerRegisterRequest $request, ViewerRegistrationService $service): RedirectResponse
    {
        // Register the user using the registration service
        $user = $service->register($request->validated());

        // Fire the registered event for the new user
        event(new Registered($user));

        // Automatically log in the newly registered user
        Auth::login($user);

        // Redirect to the home page
        return redirect(RouteServiceProvider::HOME);
    }

    /**
     * Check NIK (National Identity Number) availability and return resident data
     * 
     * @param Request $request HTTP request containing NIK to check
     * @param ViewerRegistrationService $service Service for checking NIK availability
     * @return JsonResponse JSON response with availability status and resident data
     */
    public function checkNik(Request $request, ViewerRegistrationService $service): JsonResponse
    {
        // Validate NIK format - must be exactly 16 digits
        $request->validate([
            'nik' => ['required', 'string', 'size:16', 'regex:/^\d{16}$/'],
        ]);

        // Check NIK availability using the registration service
        $check = $service->checkNikAvailability($request->input('nik'));

        // If NIK is available, return resident data
        if ($check['available']) {
            $p = $check['penduduk']; // Get resident data
            return response()->json([
                'available' => true,
                'penduduk' => [
                    'nama'  => $p?->nama_lengkap,           // Full name
                    'rt'    => $p?->rt?->nomor_rt,          // RT (Neighborhood) number
                    'rw'    => $p?->rt?->rw?->nomor_rw,     // RW (Community) number
                    'desa'  => $p?->rt?->rw?->desa?->nama,  // Village name
                ],
            ]);
        }

        // If NIK is not available, return error response
        return response()->json([
            'available' => false,
            'message' => $check['message'], // Error message from service
        ], 422); // HTTP 422 Unprocessable Entity
    }
}
