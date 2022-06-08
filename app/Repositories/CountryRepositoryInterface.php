<?php


namespace App\Repositories;

use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Support\Collection;

interface CountryRepositoryInterface extends BaseRepositoryInterface
{
    public function actives(): Collection;
    public function published(): Collection;
    public function search(string $word, int $top = 25): Collection;
}
