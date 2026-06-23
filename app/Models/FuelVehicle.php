<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FuelVehicle extends Model
{
    protected $fillable = [
        'vehicle_name',
        'plate_number',
        'fuel_type',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function fuelUseEntries(): HasMany
    {
        return $this->hasMany(FuelVehicleUseEntry::class);
    }
}
