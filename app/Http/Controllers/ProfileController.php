<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $superAdminCount = User::query()->where('role', 'super_admin')->count();
        $canDeleteSelf = $user && !(strtolower((string) $user->role) === 'super_admin' && $superAdminCount <= 1);

        return view('profile.edit', [
            'user' => $user,
            'canDeleteSelf' => $canDeleteSelf,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        $request->user()->save();

        return Redirect::route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Delete the user's account.
     */
    // public function destroy(Request $request): RedirectResponse
    // {
    //     $user = $request->user();
    //     $superAdminCount = User::query()->where('role', 'super_admin')->count();
    //     $canDeleteSelf = $user && !(strtolower((string) $user->role) === 'super_admin' && $superAdminCount <= 1);

    //     if (! $canDeleteSelf) {
    //         return Redirect::route('profile.edit')
    //             ->with('error', 'Akun super admin terakhir tidak dapat dihapus.');
    //     }

    //     $request->validateWithBag('userDeletion', [
    //         'password' => ['required', 'current_password'],
    //     ]);

    //     Auth::logout();

    //     $user->delete();

    //     $request->session()->invalidate();
    //     $request->session()->regenerateToken();

    //     return Redirect::to('/');
    // }
}
