<?php


namespace App\Repositories;

use App\Models\UserContact;
use App\Repositories\Base\BaseRepository;
use Illuminate\Support\Facades\Auth;

class UserContactRepository extends BaseRepository implements UserContactRepositoryInterface
{
    public function __construct(UserContact $model)
    {
        parent::__construct($model);
    }

    public function user_is_blocked ($userid)
    {
        return UserContact::where('contact_id', $userid)->where('user_id', Auth::id())->where('status','Block')->orderBy('id','DESC')->first();
    }

    public function get_by_user_and_contact ($user_id, $contact_id)
    {
        return UserContact::where('user_id', $user_id)
        ->where('contact_id', $contact_id)
        ->orderBy('id','DESC')->first();
    }
}
