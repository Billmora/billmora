<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Currency::updateOrCreate(
            ['code' => 'USD'],
            [
                'prefix' => '$',
                'suffix' => null,
                'format' => '1,234.56',
                'base_rate' => 1.00000000,
                'is_default' => true,
            ]
        );
    }
}
