<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FinancialYear;
use Carbon\Carbon;

class FinancialYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $currentYear = now()->year;
        $currentMonth = now()->month;

        // Assuming financial year starts on April 1
        if ($currentMonth < 4) {
            $startYear = $currentYear - 1;
        } else {
            $startYear = $currentYear;
        }

        $currentFY = [
            'year_range' => $startYear . '-' . ($startYear + 1),
            'start_date' => Carbon::create($startYear, 4, 1),
            'end_date'   => Carbon::create($startYear + 1, 3, 31),
            'is_active'  => true,
        ];

        $nextFY = [
            'year_range' => ($startYear + 1) . '-' . ($startYear + 2),
            'start_date' => Carbon::create($startYear + 1, 4, 1),
            'end_date'   => Carbon::create($startYear + 2, 3, 31),
            'is_active'  => false,
        ];

        FinancialYear::create($currentFY);
        FinancialYear::create($nextFY);
    }
}
