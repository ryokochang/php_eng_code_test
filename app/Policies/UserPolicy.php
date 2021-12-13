<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    protected $checkAccess = ['admin'];
    protected $checkStatus = ['approved'];

    public function allowAccess(User $accessingUser)
    {
        return in_array($accessingUser->role, $this->checkAccess);
    }

    public function allowLogin(User $accessingUser)
    {
        return in_array($accessingUser->status, $this->checkStatus);
    }
}
