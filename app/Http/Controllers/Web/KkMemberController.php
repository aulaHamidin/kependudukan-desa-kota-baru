<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\DataInti\KkMember\LeaveRequest;
use App\Http\Requests\DataInti\KkMember\StoreRequest;
use App\Http\Requests\DataInti\KkMember\UpdateRequest;
use App\Models\KkMember;
use App\Services\KkMemberService;
use DomainException;
use Illuminate\Http\RedirectResponse;

class KkMemberController extends Controller
{
    public function __construct(private KkMemberService $service)
    {
        $this->authorizeResource(KkMember::class, 'kk_member');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        try {
            $member = $this->service->addMember($request->user(), $request->validated());

            return redirect()
                ->back()
                ->with('success', 'Anggota KK berhasil ditambahkan.');
        } catch (DomainException $exception) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }
    }

    public function update(UpdateRequest $request, KkMember $kkMember): RedirectResponse
    {
        try {
            $member = $this->service->updateMember($request->user(), $kkMember, $request->validated());

            return redirect()
                ->back()
                ->with('success', 'Anggota KK berhasil diperbarui.');
        } catch (DomainException $exception) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }
    }

    public function leave(LeaveRequest $request, KkMember $kkMember): RedirectResponse
    {
        try {
            $member = $this->service->removeMember($request->user(), $kkMember, $request->validated());

            return redirect()
                ->back()
                ->with('success', 'Anggota KK berhasil dikeluarkan.');
        } catch (DomainException $exception) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }
    }

    public function setKepala(KkMember $kkMember): RedirectResponse
    {
        try {
            $member = $this->service->setKepalaKeluarga(auth()->user(), $kkMember);

            return redirect()
                ->back()
                ->with('success', 'Kepala keluarga berhasil diperbarui.');
        } catch (DomainException $exception) {
            return redirect()
                ->back()
                ->with('error', $exception->getMessage());
        }
    }

}
