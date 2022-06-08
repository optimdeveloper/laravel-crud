<?php


namespace App\Repositories\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface BaseRepositoryInterface
{

    public function me($id, $relation=null, $filter = null): Collection;

    public function create(array $attributes): Model;

    public function find($id): ?Model;

    public function all(): Collection;

    public function actives(): Collection;

    public function published(): Collection;

    public function restore(int $id): bool;

    public function save(Model $model): bool;

    public function delete(int $id): bool;

    public function deleteLogic(int $id): bool;

    public function activesPaged(int $start, int $length, string $order_by, string $order, string $search): array;

    public function activesPagedByTenant(int $tenant_id, int $start, int $length, string $order_by, string $order, string $search): array;

    public function activesByTenant(int $tenant_id): Collection;

    public function truncate(): void;
}
