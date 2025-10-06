<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LivestockStatistic;
use App\Models\Region;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LivestockStatisticSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates realistic livestock statistics for CARAGA Region provinces (2023-2025)
     * Based on actual CARAGA livestock production profiles
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Get CARAGA regions/provinces
            $agusanNorte = Region::where('code', 'AGN')->first();
            $agusanSur = Region::where('code', 'AGS')->first();
            $surigaoNorte = Region::where('code', 'SUN')->first();
            $surigaoSur = Region::where('code', 'SUS')->first();
            $dinagatIslands = Region::where('code', 'DIN')->first();

            $livestockData = [];

            // Define provinces with their livestock population characteristics
            $provinces = [
                [
                    'region' => $agusanNorte,
                    'name' => 'Agusan del Norte',
                    'livestock' => [
                        'Cattle' => ['min' => 95000, 'max' => 115000],
                        'Carabao' => ['min' => 62000, 'max' => 78000],
                        'Swine' => ['min' => 245000, 'max' => 285000],
                        'Goat' => ['min' => 48000, 'max' => 58000],
                        'Poultry' => ['min' => 1250000, 'max' => 1450000],
                    ]
                ],
                [
                    'region' => $agusanSur,
                    'name' => 'Agusan del Sur',
                    'livestock' => [
                        'Cattle' => ['min' => 88000, 'max' => 108000],
                        'Carabao' => ['min' => 58000, 'max' => 74000],
                        'Swine' => ['min' => 225000, 'max' => 265000],
                        'Goat' => ['min' => 42000, 'max' => 54000],
                        'Poultry' => ['min' => 1150000, 'max' => 1350000],
                    ]
                ],
                [
                    'region' => $surigaoNorte,
                    'name' => 'Surigao del Norte',
                    'livestock' => [
                        'Cattle' => ['min' => 78000, 'max' => 98000],
                        'Carabao' => ['min' => 52000, 'max' => 68000],
                        'Swine' => ['min' => 205000, 'max' => 245000],
                        'Goat' => ['min' => 38000, 'max' => 48000],
                        'Poultry' => ['min' => 1050000, 'max' => 1250000],
                    ]
                ],
                [
                    'region' => $surigaoSur,
                    'name' => 'Surigao del Sur',
                    'livestock' => [
                        'Cattle' => ['min' => 72000, 'max' => 92000],
                        'Carabao' => ['min' => 48000, 'max' => 64000],
                        'Swine' => ['min' => 185000, 'max' => 225000],
                        'Goat' => ['min' => 35000, 'max' => 45000],
                        'Poultry' => ['min' => 950000, 'max' => 1150000],
                    ]
                ],
                [
                    'region' => $dinagatIslands,
                    'name' => 'Dinagat Islands',
                    'livestock' => [
                        'Cattle' => ['min' => 28000, 'max' => 38000],
                        'Carabao' => ['min' => 22000, 'max' => 32000],
                        'Swine' => ['min' => 85000, 'max' => 115000],
                        'Goat' => ['min' => 18000, 'max' => 26000],
                        'Poultry' => ['min' => 450000, 'max' => 650000],
                    ]
                ],
            ];

            // Generate data for years 2023, 2024, 2025
            $years = [2023, 2024, 2025];

            // Recording periods (quarterly census)
            $quarters = [
                ['Q1', 3, 'January-March Census'],
                ['Q2', 6, 'April-June Census'],
                ['Q3', 9, 'July-September Census'],
                ['Q4', 12, 'October-December Census'],
            ];

            foreach ($provinces as $province) {
                if (!$province['region']) continue;

                foreach ($years as $year) {
                    foreach ($province['livestock'] as $livestockType => $range) {
                        // Base population with growth trend
                        $growthFactor = 1 + (($year - 2023) * 0.03); // 3% annual growth
                        $basePopulation = rand($range['min'], $range['max']) * $growthFactor;

                        // Generate quarterly census data
                        foreach ($quarters as $quarter) {
                            // Quarterly variation (±5%)
                            $variation = (rand(-5, 5) / 100);
                            $quarterlyPopulation = $basePopulation * (1 + $variation);

                            $recordedDate = Carbon::create($year, $quarter[1], rand(1, 28));

                            $livestockData[] = [
                                'region_id' => $province['region']->id,
                                'livestock_type' => $livestockType,
                                'population' => round($quarterlyPopulation),
                                'unit' => 'heads',
                                'recorded_date' => $recordedDate,
                                'fiscal_year' => $year,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }

                    // Add other poultry types for diversity
                    $otherPoultry = [
                        ['type' => 'Native Chicken', 'min' => 180000, 'max' => 250000],
                        ['type' => 'Duck', 'min' => 45000, 'max' => 75000],
                    ];

                    foreach ($otherPoultry as $poultryType) {
                        $growthFactor = 1 + (($year - 2023) * 0.03);
                        $basePopulation = rand($poultryType['min'], $poultryType['max']) * $growthFactor;

                        // Annual census for other poultry
                        $recordedDate = Carbon::create($year, 6, 15);

                        $livestockData[] = [
                            'region_id' => $province['region']->id,
                            'livestock_type' => $poultryType['type'],
                            'population' => round($basePopulation),
                            'unit' => 'heads',
                            'recorded_date' => $recordedDate,
                            'fiscal_year' => $year,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            // Additional specialized livestock for specific provinces

            // Dairy cattle in Agusan provinces
            $dairyProvinces = [$agusanNorte, $agusanSur];
            foreach ($dairyProvinces as $region) {
                if (!$region) continue;
                foreach ($years as $year) {
                    $growthFactor = 1 + (($year - 2023) * 0.05); // 5% growth for dairy
                    $livestockData[] = [
                        'region_id' => $region->id,
                        'livestock_type' => 'Dairy Cattle',
                        'population' => round(rand(3500, 5500) * $growthFactor),
                        'unit' => 'heads',
                        'recorded_date' => Carbon::create($year, 6, 15),
                        'fiscal_year' => $year,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Sheep in upland areas
            $sheepProvinces = [$surigaoSur, $agusanSur];
            foreach ($sheepProvinces as $region) {
                if (!$region) continue;
                foreach ($years as $year) {
                    $growthFactor = 1 + (($year - 2023) * 0.04);
                    $livestockData[] = [
                        'region_id' => $region->id,
                        'livestock_type' => 'Sheep',
                        'population' => round(rand(2500, 4500) * $growthFactor),
                        'unit' => 'heads',
                        'recorded_date' => Carbon::create($year, 6, 15),
                        'fiscal_year' => $year,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Insert all livestock statistics
            foreach ($livestockData as $data) {
                LivestockStatistic::create($data);
            }
        });

        $this->command->info('CARAGA livestock statistics seeded successfully!');
        $this->command->info('Data generated for 2023-2025 covering Cattle, Carabao, Swine, Goats, Poultry, and other livestock');
    }
}

