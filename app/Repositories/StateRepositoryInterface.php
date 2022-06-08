<?php


namespace App\Repositories;

use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Support\Collection;

interface StateRepositoryInterface extends BaseRepositoryInterface
{
    public function actives(): Collection;
    public function published(): Collection;
    public function publishedByCountry($country_id): Collection;
}
