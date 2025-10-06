<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CropProduction;
use App\Models\Region;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CropProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates realistic crop production data for CARAGA Region provinces (2023-2025)
     * Data based on actual CARAGA agricultural production profiles
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Get CARAGA regions/provinces
            $caraga = Region::where('code', 'XIII')->first();
            $agusanNorte = Region::where('code', 'AGN')->first();
            $agusanSur = Region::where('code', 'AGS')->first();
            $surigaoNorte = Region::where('code', 'SUN')->first();
            $surigaoSur = Region::where('code', 'SUS')->first();
            $dinagatIslands = Region::where('code', 'DIN')->first();

            $cropData = [];

            // Define provinces with their crop production characteristics
            $provinces = [
                [
                    'region' => $agusanNorte,
                    'name' => 'Agusan del Norte',
                    'crops' => [
                        'Rice' => ['min' => 180000, 'max' => 220000],
                        'Corn' => ['min' => 95000, 'max' => 125000],
                        'Coconut' => ['min' => 65000, 'max' => 85000],
                        'Banana' => ['min' => 42000, 'max' => 58000],
                    ]
                ],
                [
                    'region' => $agusanSur,
                    'name' => 'Agusan del Sur',
                    'crops' => [
                        'Rice' => ['min' => 165000, 'max' => 205000],
                        'Corn' => ['min' => 88000, 'max' => 118000],
                        'Coconut' => ['min' => 58000, 'max' => 78000],
                        'Banana' => ['min' => 38000, 'max' => 52000],
                    ]
                ],
                [
                    'region' => $surigaoNorte,
                    'name' => 'Surigao del Norte',
                    'crops' => [
                        'Rice' => ['min' => 155000, 'max' => 195000],
                        'Corn' => ['min' => 75000, 'max' => 105000],
                        'Coconut' => ['min' => 72000, 'max' => 92000],
                        'Banana' => ['min' => 35000, 'max' => 48000],
                    ]
                ],
                [
                    'region' => $surigaoSur,
                    'name' => 'Surigao del Sur',
                    'crops' => [
                        'Rice' => ['min' => 145000, 'max' => 185000],
                        'Corn' => ['min' => 68000, 'max' => 98000],
                        'Coconut' => ['min' => 55000, 'max' => 75000],
                        'Cacao' => ['min' => 6500, 'max' => 9500],
                        'Coffee' => ['min' => 4500, 'max' => 7500],
                        'Banana' => ['min' => 32000, 'max' => 45000],
                    ]
                ],
                [
                    'region' => $dinagatIslands,
                    'name' => 'Dinagat Islands',
                    'crops' => [
                        'Rice' => ['min' => 48000, 'max' => 68000],
                        'Corn' => ['min' => 32000, 'max' => 48000],
                        'Coconut' => ['min' => 45000, 'max' => 65000],
                        'Abaca' => ['min' => 9500, 'max' => 14500],
                        'Banana' => ['min' => 18000, 'max' => 28000],
                    ]
                ],
            ];

            // Generate data for years 2023, 2024, 2025
            $years = [2023, 2024, 2025];
            $quarters = [
                ['Q1', 1, 'January-March'],
                ['Q2', 4, 'April-June'],
                ['Q3', 7, 'July-September'],
                ['Q4', 10, 'October-December'],
            ];

            foreach ($provinces as $province) {
                if (!$province['region']) continue;

                foreach ($years as $year) {
                    foreach ($province['crops'] as $cropName => $range) {
                        // Annual production with seasonal variation
                        $annualProduction = rand($range['min'], $range['max']);

                        // Distribute across quarters with realistic seasonal patterns
                        foreach ($quarters as $quarter) {
                            $seasonalFactor = $this->getSeasonalFactor($cropName, $quarter[0]);
                            $quarterlyProduction = $annualProduction * $seasonalFactor;

                            $harvestMonth = $quarter[1] + rand(0, 2); // Random month in quarter
                            $harvestDate = Carbon::create($year, $harvestMonth, rand(1, 28));

                            $cropData[] = [
                                'region_id' => $province['region']->id,
                                'crop_name' => $cropName,
                                'production_volume' => round($quarterlyProduction, 2),
                                'unit' => 'metric tons',
                                'harvest_date' => $harvestDate,
                                'fiscal_year' => $year,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }
            }

            // Additional high-value crops for specific provinces
            // Cacao for other provinces
            $cacaoProvinces = [$agusanNorte, $agusanSur];
            foreach ($cacaoProvinces as $region) {
                if (!$region) continue;
                foreach ($years as $year) {
                    $cropData[] = [
                        'region_id' => $region->id,
                        'crop_name' => 'Cacao',
                        'production_volume' => rand(4000, 7000),
                        'unit' => 'metric tons',
                        'harvest_date' => Carbon::create($year, rand(3, 11), rand(1, 28)),
                        'fiscal_year' => $year,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Coffee for northern provinces
            $coffeeProvinces = [$agusanNorte, $surigaoNorte];
            foreach ($coffeeProvinces as $region) {
                if (!$region) continue;
                foreach ($years as $year) {
                    $cropData[] = [
                        'region_id' => $region->id,
                        'crop_name' => 'Coffee',
                        'production_volume' => rand(3000, 5500),
                        'unit' => 'metric tons',
                        'harvest_date' => Carbon::create($year, rand(10, 12), rand(1, 28)),
                        'fiscal_year' => $year,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Abaca for coastal provinces
            $abacaProvinces = [$surigaoNorte, $surigaoSur];
            foreach ($abacaProvinces as $region) {
                if (!$region) continue;
                foreach ($years as $year) {
                    $cropData[] = [
                        'region_id' => $region->id,
                        'crop_name' => 'Abaca',
                        'production_volume' => rand(7000, 11000),
                        'unit' => 'metric tons',
                        'harvest_date' => Carbon::create($year, rand(1, 12), rand(1, 28)),
                        'fiscal_year' => $year,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Insert all crop production data
            foreach ($cropData as $data) {
                CropProduction::create($data);
            }
        });

        $this->command->info('CARAGA crop production data seeded successfully!');
        $this->command->info('Data generated for 2023-2025 covering Rice, Corn, Coconut, Banana, Cacao, Coffee, and Abaca');
    }

    /**
     * Get seasonal factor for crop production
     */
    private function getSeasonalFactor(string $cropName, string $quarter): float
    {
        $seasonalPatterns = [
            'Rice' => ['Q1' => 0.30, 'Q2' => 0.35, 'Q3' => 0.20, 'Q4' => 0.15], // Peak harvest Q1-Q2
            'Corn' => ['Q1' => 0.25, 'Q2' => 0.30, 'Q3' => 0.25, 'Q4' => 0.20], // Two cropping seasons
            'Coconut' => ['Q1' => 0.25, 'Q2' => 0.25, 'Q3' => 0.25, 'Q4' => 0.25], // Year-round
            'Banana' => ['Q1' => 0.25, 'Q2' => 0.25, 'Q3' => 0.25, 'Q4' => 0.25], // Year-round
            'Cacao' => ['Q1' => 0.20, 'Q2' => 0.30, 'Q3' => 0.30, 'Q4' => 0.20], // Peak mid-year
            'Coffee' => ['Q1' => 0.15, 'Q2' => 0.20, 'Q3' => 0.25, 'Q4' => 0.40], // Peak Q4
            'Abaca' => ['Q1' => 0.25, 'Q2' => 0.25, 'Q3' => 0.25, 'Q4' => 0.25], // Year-round
        ];

        return $seasonalPatterns[$cropName][$quarter] ?? 0.25;
    }
}

