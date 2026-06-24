<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelVehicleUseEntry extends Model
{
    protected $fillable = [
        'fuel_vehicle_use_id',
        'fuel_vehicle_id',
        'fuel_cost',
        'fuel_liters',
    ];

    protected $casts = [
        'fuel_cost' => 'decimal:2',
        'fuel_liters' => 'decimal:2',
    ];

    public function fuelVehicleUse(): BelongsTo
    {
        return $this->belongsTo(FuelVehicleUse::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(FuelVehicle::class, 'fuel_vehicle_id');
    }
}
