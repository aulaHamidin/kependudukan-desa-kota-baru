<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Administrator;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Models\Desa;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Services\UserService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private UserService $service)
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request): View
    {
        $actor = $request->user();
        $users = $this->service->listForIndex($actor);

        return view('administrator.kelola_user.index', [
            'users' => $users,
            'allowedRoles' => $this->getAllowedRoles($actor),
            'territories' => $this->getAvailableTerritories($actor),
        ]);
    }

    public function create(Request $request): View
    {
        $actor = $request->user();

        return view('administrator.kelola_user.create', [
            'allowedRoles' => $this->getAllowedRoles($actor),
            'territories' => $this->getAvailableTerritories($actor),
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        try {
            $this->service->store($request->user(), $request->validated());

            return redirect()
                ->route('administrator.kelola-user.index')
                ->with('success', 'User berhasil ditambahkan.');
        } catch (DomainException $exception) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }
    }

    public function edit(User $user, Request $request): View
    {
        $actor = $request->user();

        return view('administrator.kelola_user.edit', [
            'user' => $user,
            'allowedRoles' => $this->getAllowedRoles($actor),
            'territories' => $this->getAvailableTerritories($actor),
        ]);
    }

    public function update(UpdateRequest $request, User $user): RedirectResponse
    {
        try {
            $this->service->update($request->user(), $user->id, $request->validated());

            return redirect()
                ->route('administrator.kelola-user.index')
                ->with('success', 'User berhasil diperbarui.');
        } catch (DomainException $exception) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }
    }

    public function destroy(User $user, Request $request): RedirectResponse
    {
        try {
            $this->service->delete($request->user(), $user->id);

            return redirect()
                ->route('administrator.kelola-user.index')
                ->with('success', 'User berhasil dihapus.');
        } catch (DomainException $exception) {
            return redirect()
                ->back()
                ->with('error', $exception->getMessage());
        }
    }

    public function restore(string $user, Request $request): RedirectResponse
    {
        $target = User::withTrashed()->findOrFail((int) $user);

        $this->authorize('restore', $target);

        if ($target->trashed()) {
            $target->restore();
        }

        return redirect()
            ->route('administrator.kelola-user.index')
            ->with('success', 'User berhasil dikembalikan.');
    }

    private function getAllowedRoles(User $actor): array
    {
        return match ($actor->role) {
            'super_admin' => ['super_admin', 'admin_desa', 'admin_rw'],
            'admin_desa' => ['admin_rw', 'admin_rt', 'viewer'],
            'admin_rw' => ['admin_rt', 'viewer'],
            default => [],
        };
    }

    private function getAvailableTerritories(User $actor): array
    {
        $territories = [
            'desas' => collect(),
            'rws' => collect(),
            'rts' => collect(),
        ];

        if ($actor->role === 'super_admin') {
            $territories['desas'] = Desa::query()->orderBy('nama')->get();
            $territories['rws'] = Rw::query()->with('desa')->orderBy('nomor_rw')->get();
            $territories['rts'] = Rt::query()->with('rw.desa')->orderBy('nomor_rt')->get();
        }

        if ($actor->role === 'admin_desa') {
            $territories['desas'] = Desa::query()->where('id', $actor->desa_id)->get();
            $territories['rws'] = Rw::query()->where('desa_id', $actor->desa_id)->get();
            $territories['rts'] = Rt::query()
                ->whereHas('rw', fn($q) => $q->where('desa_id', $actor->desa_id))
                ->with('rw')
                ->get();
        }

        if ($actor->role === 'admin_rw') {
            $territories['rts'] = Rt::query()->where('rw_id', $actor->rw_id)->get();
        }

        return $territories;
    }
}
