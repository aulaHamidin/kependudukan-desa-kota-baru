<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface EventRepositoryInterface
{
    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Event;

    public function create(array $data): Event;

    public function update(Event $event, array $data): bool;

    public function updateStatus(Event $event, string $status, ?int $verifiedBy = null): bool;

    public function voidEvent(Event $event, string $reason): bool;

    public function findPendingByUser(User $user): Collection;

    public function findEditableByUser(User $user): Collection;

    public function getByType(string $eventTypeCode): Collection;

    /**
     * Paginate Event Datang with filters (status, date, RT, search)
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateDatangWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;
}
