<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WaterBill;

class WaterBillPolicy
{
    public function view(User $user, WaterBill $waterBill): bool
    {
        return $user->isAdmin() || (int) $waterBill->user_id === (int) $user->id;
    }

    public function update(User $user, WaterBill $waterBill): bool
    {
        return $user->isAdmin() || (int) $waterBill->user_id === (int) $user->id;
    }

    public function delete(User $user, WaterBill $waterBill): bool
    {
        return $user->isAdmin() || (int) $waterBill->user_id === (int) $user->id;
    }
}
