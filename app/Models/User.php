<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    public const REPORT_TYPES = [
        'fuel-prices' => 'Weekly Fuel Prices',
        'electricity-consumptions' => 'Electricity Consumption',
        'fuel-vehicle-uses' => 'Fuel and Vehicle Use',
        'solar-performances' => 'Solar Savings',
        'student-service-volumes' => 'Student Service Volume',
        'estimated-savings' => 'Estimated Savings',
    ];

    protected $fillable = [
        'name',
        'office_name',
        'email',
        'password',
        'role',
        'approved_at',
        'report_types',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'report_types' => 'array',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    public function allowedReportTypes(): array
    {
        if ($this->isAdmin()) {
            return array_keys(self::REPORT_TYPES);
        }

        return array_values(array_intersect($this->report_types ?? [], array_keys(self::REPORT_TYPES)));
    }

    public function canAccessReportType(string $reportType): bool
    {
        return in_array($reportType, $this->allowedReportTypes(), true);
    }

    public function reportTypeLabels(): array
    {
        return collect($this->allowedReportTypes())
            ->map(fn (string $key) => self::REPORT_TYPES[$key])
            ->all();
    }
}
