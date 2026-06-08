<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\MasterReference\StoreRequest;
use App\Http\Requests\MasterData\MasterReference\UpdateRequest;
use App\Services\MasterData\MasterReferenceService;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class MasterReferenceController extends Controller
{
    protected MasterReferenceService $service;

    abstract protected function getViewPrefix(): string;

    abstract protected function getRouteName(): string;

    abstract protected function getEntityName(): string;

    public function index(Request $request): View
    {
        $filters = $request->only(['is_active', 'search']);
        $filters['per_page'] = (int) $request->input('per_page', 15);

        $items = $this->service->getAll($filters);

        return view($this->getViewPrefix() . '.index', [
            'items' => $items,
            'filters' => $filters,
            'entityName' => $this->getEntityName(),
        ]);
    }

    public function create(): View
    {
        return view($this->getViewPrefix() . '.create', [
            'entityName' => $this->getEntityName(),
        ]);
    }

    public function show(string $kode): View|RedirectResponse
    {
        try {
            $item = $this->service->getById($kode);

            return view($this->getViewPrefix() . '.show', [
                'item' => $item,
                'entityName' => $this->getEntityName(),
            ]);
        } catch (ModelNotFoundException $exception) {
            return redirect()
                ->route($this->getRouteName() . '.index')
                ->with('error', $this->getEntityName() . ' tidak ditemukan.');
        }
    }

    public function edit(string $kode): View|RedirectResponse
    {
        try {
            $item = $this->service->getById($kode);

            return view($this->getViewPrefix() . '.edit', [
                'item' => $item,
                'entityName' => $this->getEntityName(),
            ]);
        } catch (ModelNotFoundException $exception) {
            return redirect()
                ->route($this->getRouteName() . '.index')
                ->with('error', $this->getEntityName() . ' tidak ditemukan.');
        }
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        try {
            $this->service->store($request->validated());

            return redirect()
                ->route($this->getRouteName() . '.index')
                ->with('success', $this->getEntityName() . ' berhasil ditambahkan.');
        } catch (DomainException $exception) {
            return back()
                ->withInput()
                ->withErrors(['message' => $exception->getMessage()]);
        } catch (\Throwable $exception) {
            return back()
                ->withInput()
                ->withErrors(['message' => 'Gagal menambahkan ' . $this->getEntityName() . '.']);
        }
    }

    public function update(UpdateRequest $request, string $kode): RedirectResponse
    {
        try {
            $this->service->update($kode, $request->validated());

            return redirect()
                ->route($this->getRouteName() . '.index')
                ->with('success', $this->getEntityName() . ' berhasil diperbarui.');
        } catch (ModelNotFoundException $exception) {
            return redirect()
                ->route($this->getRouteName() . '.index')
                ->with('error', $this->getEntityName() . ' tidak ditemukan.');
        } catch (DomainException $exception) {
            return back()
                ->withInput()
                ->withErrors(['message' => $exception->getMessage()]);
        } catch (\Throwable $exception) {
            return back()
                ->withInput()
                ->withErrors(['message' => 'Gagal memperbarui ' . $this->getEntityName() . '.']);
        }
    }

    public function destroy(string $kode): RedirectResponse
    {
        try {
            $this->service->delete($kode);

            return redirect()
                ->route($this->getRouteName() . '.index')
                ->with('success', $this->getEntityName() . ' berhasil dihapus.');
        } catch (ModelNotFoundException $exception) {
            return redirect()
                ->route($this->getRouteName() . '.index')
                ->with('error', $this->getEntityName() . ' tidak ditemukan.');
        } catch (DomainException $exception) {
            return redirect()
                ->route($this->getRouteName() . '.index')
                ->with('error', $exception->getMessage());
        } catch (\Throwable $exception) {
            return redirect()
                ->route($this->getRouteName() . '.index')
                ->with('error', 'Gagal menghapus ' . $this->getEntityName() . '.');
        }
    }
}
