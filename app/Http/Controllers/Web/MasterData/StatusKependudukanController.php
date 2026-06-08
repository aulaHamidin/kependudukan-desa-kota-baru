<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StatusKependudukan\StatusKependudukanUpdateRequest;
use App\Models\StatusKependudukan;
use App\Services\MasterData\StatusKependudukanService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatusKependudukanController extends Controller
{
    public function __construct(private StatusKependudukanService $service) {}

    public function index(Request $request): View
    {
        $items = $this->service->list();

        return view('master_data.status.index', [
            'items' => $items,
            'entityName' => 'Status Kependudukan',
            'coreStatuses' => StatusKependudukanService::CORE_STATUSES,
        ]);
    }

    public function show(StatusKependudukan $status_kependudukan): View
    {
        return view('master_data.status.show', [
            'item' => $status_kependudukan,
            'entityName' => 'Status Kependudukan',
            'coreStatuses' => StatusKependudukanService::CORE_STATUSES,
        ]);
    }

    public function edit(StatusKependudukan $status_kependudukan): View
    {
        return view('master_data.status.edit', [
            'item' => $status_kependudukan,
            'entityName' => 'Status Kependudukan',
            'coreStatuses' => StatusKependudukanService::CORE_STATUSES,
        ]);
    }

    public function update(StatusKependudukanUpdateRequest $request, StatusKependudukan $status_kependudukan): RedirectResponse
    {
        try {
            $this->service->update($status_kependudukan->kode, $request->validated());

            return redirect()
                ->route('master_data.status.index')
                ->with('success', 'Status kependudukan berhasil diperbarui.');
        } catch (DomainException $exception) {
            return back()
                ->withInput()
                ->withErrors(['message' => $exception->getMessage()]);
        } catch (\Throwable $exception) {
            return back()
                ->withInput()
                ->withErrors(['message' => 'Gagal memperbarui status kependudukan.']);
        }
    }

    public function destroy(StatusKependudukan $status_kependudukan): RedirectResponse
    {
        try {
            $this->service->deactivate($status_kependudukan->kode);

            return redirect()
                ->route('master_data.status.index')
                ->with('success', 'Status kependudukan berhasil dinonaktifkan.');
        } catch (DomainException $exception) {
            return redirect()
                ->route('master_data.status.index')
                ->with('error', $exception->getMessage());
        } catch (\Throwable $exception) {
            return redirect()
                ->route('master_data.status.index')
                ->with('error', 'Gagal menonaktifkan status kependudukan.');
        }
    }
}
