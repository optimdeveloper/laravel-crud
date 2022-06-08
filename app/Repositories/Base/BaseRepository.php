<?php


namespace App\Repositories\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Throwable;

class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find($id): ?Model
    {
        return $this->model->find($id);
    }

    public function restore($id): bool
    {
        try {
            $for_restore = $this->model::onlyTrashed()->find($id);
            if (isset($for_restore)) {
                $this->model::onlyTrashed()->find($id)->restore();
                return true;
            }
        } catch (Throwable $ex) {
            echo $ex;
            return false;
        }
    }

    public function delete($id): bool
    {
        return $this->find($id)->forceDelete();
    }

    public function deleteLogic($id): bool
    {
        try {
            $for_remove = $this->model->find($id);
            if (isset($for_remove)) {
                $this->find($id)->delete();
                return true;
            }
        } catch (Throwable $ex) {
            return false;
        }
    }

    public function save(Model $model): bool
    {
        return $model->save();
    }

    public function me($id, $relation = null, $filter = ''): Collection
    {
        if ($relation == null)
        {
            if (strtolower($filter) == 'current') {
                return $this->model
                        ->where('user_id', $id)
                        ->where('date_time', '>=', Carbon::now())
                        ->get();
            }
            if (strtolower($filter) == 'past') {
                return $this->model
                        ->where('user_id', $id)
                        ->where('date_time', '<', Carbon::now())
                        ->get();
            }
            return $this->model->where('user_id', $id)->get();
        }

        if (strtolower($filter) == 'current') {
            // dd('hola');
            return $this->model
                    ->where('user_id', $id)
                    ->where('date_time', '>=', Carbon::now())
                    ->with($relation)
                    ->get();
        }
        if (strtolower($filter) == 'past') {
            return $this->model
                    ->where('user_id', $id)
                    ->where('date_time', '<', Carbon::now())
                    ->with($relation)
                    ->get();
        }

        return $this->model->where('user_id', $id)->with($relation)->get();
    }







    public function create(array $attributes): Model
    {
        return $this->model->create($attributes);
    }

    public function actives(): Collection
    {
        return $this->model->where('deleted', 0)->get();
    }

    public function published(): Collection
    {
        return $this->model->where('deleted', 0)
            ->where('published', 1)
            ->get();
    }




    public function activesPaged(int $start, int $length, string $order_by, string $order, string $search): array
    {
        $query = $this->model->where('deleted', 0)
            ->where('name', 'like', $search . '%')
            ->skip($start)
            ->take($length);

        // 'asc'
        if (!empty($order_by))
            $query = $query->orderBy($order_by, $order);

        $query = $query->get();

        $count = $this->model->count();

        return [
            "data" => $query,
            "count" => $count
        ];
    }

    public function activesPagedByTenant(int $tenant_id, int $start, int $length, string $order_by, string $order, string $search): array
    {
        $query = $this->model->where('deleted', 0)
            ->where('name', 'like', $search . '%')
            ->where(function ($query) use ($tenant_id) {
                $query->where("tenant_id", "=", $tenant_id)
                    ->orWhere('tenant_id', "=", null)
                    ->orWhere('tenant_id', "=", 0);
            })
            ->skip($start)
            ->take($length);

        if (!empty($order_by))
            $query = $query->orderBy($order_by, $order);

        $query = $query->get();

        $count = $this->model->count();

        return [
            "data" => $query,
            "count" => $count
        ];
    }

    public function activesByTenant(int $tenant_id): Collection
    {
        return $this->model->where('deleted', 0)
            ->where(function ($query) use ($tenant_id) {
                $query->where("tenant_id", "=", $tenant_id)
                    ->orWhere('tenant_id', "=", null)
                    ->orWhere('tenant_id', "=", 0);
            })
            ->orderBy('name', 'asc')
            ->get();
    }

    public function truncate(): void
    {
        $this->model->truncate();
    }
}
