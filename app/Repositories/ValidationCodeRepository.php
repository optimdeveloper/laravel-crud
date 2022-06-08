<?php


namespace App\Repositories;

use App\Models\ValidationCode;
use App\Repositories\Base\BaseWithUserRepository;
use Illuminate\Database\Eloquent\Collection;

class ValidationCodeRepository extends BaseWithUserRepository implements ValidationCodeRepositoryInterface
{
    public function __construct(ValidationCode $model)
    {
        parent::__construct($model);
    }

    public function find_code_user($id) : Collection {
        $data = $this->model->where('user_id', $id)
        ->orderBy('created_at', 'desc')
        ->get();
        // dd($data);
        return $data;
    }

}
