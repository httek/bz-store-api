<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserService extends Service
{
    /**
     * @param int $uId
     * @param int $droplet
     * @return int
     */
    public static function incrementDroplet(int $uId, int $droplet = 0): int
    {
        $member = User::find($uId);
        $member->update(['droplet' => DB::raw("droplet + {$droplet}")]);
        return $droplet;
    }
}
