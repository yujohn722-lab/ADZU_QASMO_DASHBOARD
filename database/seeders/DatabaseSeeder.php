<?php

namespace Database\Seeders;

use App\Models\ElectricityConsumption;
use App\Models\EstimatedSaving;
use App\Models\FuelPrice;
use App\Models\FuelVehicleUse;
use App\Models\SolarPerformance;
use App\Models\StudentServiceVolume;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Energy Dashboard Admin',
            'email' => 'admin@example.edu',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $respondent = User::create([
            'name' => 'Juan Dela Cruz',
            'email' => 'respondent@example.edu',
            'password' => Hash::make('password'),
            'role' => 'respondent',
        ]);

        $this->seedFuelPrices($respondent);
        $this->seedElectricity($respondent);
        $this->seedFuelVehicleUse($respondent);
        $this->seedSolar($respondent);
        $this->seedStudentServices($respondent);
        $this->seedEstimatedSavings($respondent);
    }

    private function seedFuelPrices(User $user): void
    {
        $rows = [
            [2026, 1, 62.10, 64.85, 63.40, 67.20, 68.15, 61.90, 65.30, 63.75, 66.40, 63.20, 66.90, 62.30],
            [2026, 2, 62.85, 65.10, 64.15, 67.85, 68.75, 62.35, 65.70, 64.30, 66.95, 63.85, 67.40, 62.80],
            [2026, 3, 61.70, 64.40, 63.50, 67.15, 68.00, 61.60, 65.05, 63.95, 66.50, 63.35, 67.10, 62.15],
            [2026, 4, 60.95, 63.80, 62.90, 66.70, 67.55, 60.85, 64.55, 63.10, 65.95, 62.80, 66.40, 61.75],
        ];

        foreach ($rows as $row) {
            FuelPrice::create([
                'user_id' => $user->id,
                'respondent_name' => $user->name,
                'reporting_month' => 4,
                'reporting_year' => $row[0],
                'week_number' => $row[1],
                'shell_fuel_save_diesel' => $row[2],
                'shell_v_power_diesel' => $row[3],
                'shell_fuel_save_regular' => $row[4],
                'shell_v_power_premium' => $row[5],
                'shell_v_power_premium_sport' => $row[6],
                'petron_diesel_max' => $row[7],
                'petron_turbo_diesel' => $row[8],
                'petron_xtra_advance_regular' => $row[9],
                'petron_xcs_premium' => $row[10],
                'caltex_silver_regular' => $row[11],
                'caltex_platinum_premium' => $row[12],
                'caltex_diesel' => $row[13],
                'remarks' => 'Weekly price monitoring sample.',
            ]);
        }
    }

    private function seedElectricity(User $user): void
    {
        $rows = [
            [1, 2026, 10800, 8200, 7450, 6100, 3900, 2500, 1800, 6100, 5400, 4200],
            [2, 2026, 11200, 8500, 7300, 6350, 4100, 2650, 1900, 6250, 5500, 4050],
            [3, 2026, 10450, 7900, 7000, 5900, 3700, 2450, 1700, 5980, 5300, 3900],
            [4, 2026, 9900, 7600, 6820, 5700, 3500, 2300, 1650, 5700, 5100, 3725],
        ];

        foreach ($rows as $row) {
            $salvador = array_sum(array_slice($row, 2, 7));
            $kreutz = $row[9] + $row[10];

            ElectricityConsumption::create([
                'user_id' => $user->id,
                'respondent_name' => $user->name,
                'reporting_month' => $row[0],
                'reporting_year' => $row[1],
                'father_ernesto_carretero_kwh' => $row[2],
                'canisius_gonzaga_xavier_kwh' => $row[3],
                'bellarmine_campion_kwh' => $row[4],
                'senior_high_school_kwh' => $row[5],
                'sauras_kwh' => $row[6],
                'college_of_law_kwh' => $row[7],
                'jesuit_residence_kwh' => $row[8],
                'total_salvador_kwh' => $salvador,
                'grade_school_complex_kwh' => $row[9],
                'junior_high_school_kwh' => $row[10],
                'total_kreutz_kwh' => $kreutz,
                'total_lantaka_kwh' => $row[11],
                'remarks' => 'Monthly campus consumption sample.',
            ]);
        }
    }

    private function seedFuelVehicleUse(User $user): void
    {
        FuelVehicleUse::create([
            'user_id' => $user->id,
            'respondent_name' => $user->name,
            'reporting_month' => 4,
            'reporting_year' => 2026,
            'total_fuel_cost_incurred' => 185000,
            'remarks' => 'Monthly fuel cost sample.',
        ]);
    }

    private function seedSolar(User $user): void
    {
        foreach ([['SP-ROOF-01', 1, 2450, 36750], ['SP-ROOF-02', 2, 2580, 38700], ['SP-ROOF-01', 3, 2390, 35850], ['SP-ROOF-02', 4, 2700, 40500]] as $row) {
            SolarPerformance::create([
                'user_id' => $user->id,
                'respondent_name' => $user->name,
                'reporting_month' => $row[1],
                'reporting_year' => 2026,
                'solar_panel_id' => $row[0],
                'monthly_solar_energy_kwh' => $row[2],
                'estimated_savings' => $row[3],
                'remarks' => 'Solar generation sample.',
            ]);
        }
    }

    private function seedStudentServices(User $user): void
    {
        $rows = [
            ['Registrar', 1, 1280, 'Enrollment support, record requests, certifications'],
            ['Finance Office', 2, 1045, 'Assessment, payment verification, account inquiries'],
            ['Scholarship Office', 3, 620, 'Scholarship renewals, financial aid consultations'],
            ['Guidance Office', 4, 410, 'Counseling appointments, student consultations'],
        ];

        foreach ($rows as $row) {
            StudentServiceVolume::create([
                'user_id' => $user->id,
                'respondent_name' => $user->name,
                'reporting_month' => $row[1],
                'reporting_year' => 2026,
                'office_unit_name' => $row[0],
                'student_transactions_count' => $row[2],
                'service_types' => $row[3],
                'remarks' => 'Monthly service volume sample.',
            ]);
        }
    }

    private function seedEstimatedSavings(User $user): void
    {
        $rows = [
            ['Physical Plant Office', 'Adjusted utility operations and preventive scheduling', 150000, 280000, 85000],
            ['Academic Affairs', 'Reduced travel and hybrid coordination meetings', 120000, 45000, 65000],
            ['Student Affairs', 'Consolidated student activities and digital forms', 65000, 30000, 110000],
        ];

        foreach ($rows as $row) {
            EstimatedSaving::create([
                'user_id' => $user->id,
                'respondent_name' => $user->name,
                'reporting_year' => 2026,
                'office_unit_name' => $row[0],
                'savings_areas' => $row[1],
                'reduced_travel_savings' => $row[2],
                'reduced_utilities_savings' => $row[3],
                'reduced_activities_savings' => $row[4],
                'total_estimated_savings' => $row[2] + $row[3] + $row[4],
                'remarks' => 'Yearly estimated savings sample.',
            ]);
        }
    }
}
