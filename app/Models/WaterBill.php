<?php

namespace App\Models;

use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Model;

class WaterBill extends Model
{
    use OwnedByUser;

    public const FACILITY_FIELDS = [
        'lantaka_annex_a' => 'LANTAKA ANNEX A',
        'lantaka_old_4_st' => 'LANTAKA OLD 4-ST',
        'jr_kitchen' => 'JR KITCHEN',
        'main' => 'MAIN',
        'fws' => 'FWS',
        'ppo_shop' => 'PPO Shop',
        'aux_old_dorm' => 'AUX/ OLD DORM',
    ];

    protected $fillable = [
        'user_id',
        'reporting_month',
        'reporting_year',
        'responder_name',
        'lantaka_annex_a',
        'lantaka_old_4_st',
        'jr_kitchen',
        'main',
        'fws',
        'ppo_shop',
        'aux_old_dorm',
    ];

    protected $casts = [
        'reporting_month' => 'integer',
        'reporting_year' => 'integer',
        'lantaka_annex_a' => 'decimal:2',
        'lantaka_old_4_st' => 'decimal:2',
        'jr_kitchen' => 'decimal:2',
        'main' => 'decimal:2',
        'fws' => 'decimal:2',
        'ppo_shop' => 'decimal:2',
        'aux_old_dorm' => 'decimal:2',
    ];

    public function totalBill(): float
    {
        $total = 0;
        foreach (array_keys(self::FACILITY_FIELDS) as $field) {
            $total += (float) ($this->{$field} ?? 0);
        }
        return round($total, 2);
    }

    public function topContributor(): ?array
    {
        $highest = null;
        $highestField = null;

        foreach (self::FACILITY_FIELDS as $field => $label) {
            $value = $this->{$field};
            if ($value !== null && ($highest === null || $value > $highest)) {
                $highest = $value;
                $highestField = $field;
            }
        }

        return $highest !== null ? ['facility' => self::FACILITY_FIELDS[$highestField], 'amount' => $highest] : null;
    }
}
