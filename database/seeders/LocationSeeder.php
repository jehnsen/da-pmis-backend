<?php

namespace Database\Seeders;

use App\Models\Municipality;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // CARAGA Region (Region XIII)
        $caraga = Region::updateOrCreate(
            ['code' => 'XIII'],
            [
                'name' => 'CARAGA Region',
                'description' => 'Caraga Administrative Region, also known as Region XIII, is located in northeastern Mindanao.',
                'psgc_code' => '160000000',
                'latitude' => 8.8015,
                'longitude' => 125.7407,
                'is_active' => true,
            ]
        );

        // Provinces of CARAGA
        $provinces = [
            [
                'code' => 'AGN',
                'name' => 'Agusan del Norte',
                'psgc_code' => '160200000',
                'latitude' => 8.9475,
                'longitude' => 125.5283,
                'municipalities' => [
                    ['code' => 'AGN-BTN', 'name' => 'Buenavista', 'type' => 'municipality'],
                    ['code' => 'AGN-BTG', 'name' => 'Butuan', 'type' => 'huc', 'zip_code' => '8600'],
                    ['code' => 'AGN-CRN', 'name' => 'Cabadbaran', 'type' => 'city', 'zip_code' => '8605'],
                    ['code' => 'AGN-CRM', 'name' => 'Carmen', 'type' => 'municipality'],
                    ['code' => 'AGN-JBG', 'name' => 'Jabonga', 'type' => 'municipality'],
                    ['code' => 'AGN-KTN', 'name' => 'Kitcharao', 'type' => 'municipality'],
                    ['code' => 'AGN-LSF', 'name' => 'Las Nieves', 'type' => 'municipality'],
                    ['code' => 'AGN-MGL', 'name' => 'Magallanes', 'type' => 'municipality'],
                    ['code' => 'AGN-NVT', 'name' => 'Nasipit', 'type' => 'municipality', 'zip_code' => '8602'],
                    ['code' => 'AGN-RMD', 'name' => 'Remedios T. Romualdez', 'type' => 'municipality'],
                    ['code' => 'AGN-SNT', 'name' => 'Santiago', 'type' => 'municipality'],
                    ['code' => 'AGN-TBY', 'name' => 'Tubay', 'type' => 'municipality'],
                ],
            ],
            [
                'code' => 'AGS',
                'name' => 'Agusan del Sur',
                'psgc_code' => '160300000',
                'latitude' => 8.5567,
                'longitude' => 125.9800,
                'municipalities' => [
                    ['code' => 'AGS-BYB', 'name' => 'Bayugan', 'type' => 'city', 'zip_code' => '8502'],
                    ['code' => 'AGS-BNN', 'name' => 'Bunawan', 'type' => 'municipality'],
                    ['code' => 'AGS-ESP', 'name' => 'Esperanza', 'type' => 'municipality'],
                    ['code' => 'AGS-LPZ', 'name' => 'La Paz', 'type' => 'municipality'],
                    ['code' => 'AGS-LRN', 'name' => 'Loreto', 'type' => 'municipality'],
                    ['code' => 'AGS-PRR', 'name' => 'Prosperidad', 'type' => 'municipality', 'zip_code' => '8500'],
                    ['code' => 'AGS-RSR', 'name' => 'Rosario', 'type' => 'municipality'],
                    ['code' => 'AGS-SFR', 'name' => 'San Francisco', 'type' => 'municipality'],
                    ['code' => 'AGS-SLS', 'name' => 'San Luis', 'type' => 'municipality'],
                    ['code' => 'AGS-SBG', 'name' => 'Santa Josefa', 'type' => 'municipality'],
                    ['code' => 'AGS-SBT', 'name' => 'Sibagat', 'type' => 'municipality'],
                    ['code' => 'AGS-TLC', 'name' => 'Talacogon', 'type' => 'municipality'],
                    ['code' => 'AGS-TRN', 'name' => 'Trento', 'type' => 'municipality'],
                    ['code' => 'AGS-VRU', 'name' => 'Veruela', 'type' => 'municipality'],
                ],
            ],
            [
                'code' => 'SUN',
                'name' => 'Surigao del Norte',
                'psgc_code' => '166000000',
                'latitude' => 9.7833,
                'longitude' => 125.4833,
                'municipalities' => [
                    ['code' => 'SUN-ALG', 'name' => 'Alegria', 'type' => 'municipality'],
                    ['code' => 'SUN-BCL', 'name' => 'Bacuag', 'type' => 'municipality'],
                    ['code' => 'SUN-BRG', 'name' => 'Burgos', 'type' => 'municipality'],
                    ['code' => 'SUN-CLC', 'name' => 'Claver', 'type' => 'municipality'],
                    ['code' => 'SUN-DPT', 'name' => 'Dapa', 'type' => 'municipality'],
                    ['code' => 'SUN-DVC', 'name' => 'Del Carmen', 'type' => 'municipality'],
                    ['code' => 'SUN-GNL', 'name' => 'General Luna', 'type' => 'municipality'],
                    ['code' => 'SUN-GGL', 'name' => 'Gigaquit', 'type' => 'municipality'],
                    ['code' => 'SUN-MLN', 'name' => 'Mainit', 'type' => 'municipality'],
                    ['code' => 'SUN-MLT', 'name' => 'Malimono', 'type' => 'municipality'],
                    ['code' => 'SUN-PDC', 'name' => 'Pilar', 'type' => 'municipality'],
                    ['code' => 'SUN-PLS', 'name' => 'Placer', 'type' => 'municipality'],
                    ['code' => 'SUN-SBS', 'name' => 'San Benito', 'type' => 'municipality'],
                    ['code' => 'SUN-SFR', 'name' => 'San Francisco', 'type' => 'municipality'],
                    ['code' => 'SUN-SSG', 'name' => 'San Isidro', 'type' => 'municipality'],
                    ['code' => 'SUN-SNT', 'name' => 'Santa Monica', 'type' => 'municipality'],
                    ['code' => 'SUN-SGR', 'name' => 'Sison', 'type' => 'municipality'],
                    ['code' => 'SUN-SCN', 'name' => 'Socorro', 'type' => 'municipality'],
                    ['code' => 'SUN-SCT', 'name' => 'Surigao City', 'type' => 'huc', 'zip_code' => '8400'],
                    ['code' => 'SUN-TGB', 'name' => 'Tagana-an', 'type' => 'municipality'],
                    ['code' => 'SUN-TBD', 'name' => 'Tubod', 'type' => 'municipality'],
                ],
            ],
            [
                'code' => 'SUS',
                'name' => 'Surigao del Sur',
                'psgc_code' => '166700000',
                'latitude' => 8.6500,
                'longitude' => 126.1667,
                'municipalities' => [
                    ['code' => 'SUS-BSC', 'name' => 'Barobo', 'type' => 'municipality'],
                    ['code' => 'SUS-BYB', 'name' => 'Bayabas', 'type' => 'municipality'],
                    ['code' => 'SUS-BSB', 'name' => 'Bislig', 'type' => 'city', 'zip_code' => '8311'],
                    ['code' => 'SUS-CGL', 'name' => 'Cagwait', 'type' => 'municipality'],
                    ['code' => 'SUS-CNT', 'name' => 'Cantilan', 'type' => 'municipality'],
                    ['code' => 'SUS-CRM', 'name' => 'Carmen', 'type' => 'municipality'],
                    ['code' => 'SUS-CRT', 'name' => 'Carrascal', 'type' => 'municipality'],
                    ['code' => 'SUS-CRC', 'name' => 'Cortes', 'type' => 'municipality'],
                    ['code' => 'SUS-HNT', 'name' => 'Hinatuan', 'type' => 'municipality'],
                    ['code' => 'SUS-LNG', 'name' => 'Lanuza', 'type' => 'municipality'],
                    ['code' => 'SUS-LBN', 'name' => 'Lianga', 'type' => 'municipality'],
                    ['code' => 'SUS-LNG', 'name' => 'Lingig', 'type' => 'municipality'],
                    ['code' => 'SUS-MDR', 'name' => 'Madrid', 'type' => 'municipality'],
                    ['code' => 'SUS-MRN', 'name' => 'Marihatag', 'type' => 'municipality'],
                    ['code' => 'SUS-SMG', 'name' => 'San Agustin', 'type' => 'municipality'],
                    ['code' => 'SUS-SMG', 'name' => 'San Miguel', 'type' => 'municipality'],
                    ['code' => 'SUS-TGK', 'name' => 'Tagbina', 'type' => 'municipality'],
                    ['code' => 'SUS-TNG', 'name' => 'Tago', 'type' => 'municipality'],
                    ['code' => 'SUS-TND', 'name' => 'Tandag', 'type' => 'city', 'zip_code' => '8300'],
                ],
            ],
            [
                'code' => 'DIN',
                'name' => 'Dinagat Islands',
                'psgc_code' => '168500000',
                'latitude' => 10.1283,
                'longitude' => 125.6050,
                'municipalities' => [
                    ['code' => 'DIN-BSL', 'name' => 'Basilisa', 'type' => 'municipality'],
                    ['code' => 'DIN-CGL', 'name' => 'Cagdianao', 'type' => 'municipality'],
                    ['code' => 'DIN-DNG', 'name' => 'Dinagat', 'type' => 'municipality'],
                    ['code' => 'DIN-LBS', 'name' => 'Libjo', 'type' => 'municipality'],
                    ['code' => 'DIN-LRG', 'name' => 'Loreto', 'type' => 'municipality'],
                    ['code' => 'DIN-SJS', 'name' => 'San Jose', 'type' => 'municipality', 'zip_code' => '8427'],
                    ['code' => 'DIN-TBS', 'name' => 'Tubajon', 'type' => 'municipality'],
                ],
            ],
        ];

        foreach ($provinces as $provinceData) {
            $municipalities = $provinceData['municipalities'];
            unset($provinceData['municipalities']);

            $province = Province::updateOrCreate(
                ['code' => $provinceData['code']],
                array_merge($provinceData, [
                    'region_id' => $caraga->id,
                    'is_active' => true,
                ])
            );

            foreach ($municipalities as $municipalityData) {
                Municipality::updateOrCreate(
                    ['code' => $municipalityData['code']],
                    array_merge($municipalityData, [
                        'province_id' => $province->id,
                        'is_active' => true,
                    ])
                );
            }
        }

        $this->command->info('CARAGA Region location data seeded successfully!');
        $this->command->info('- 1 Region');
        $this->command->info('- 5 Provinces');
        $this->command->info('- 73 Municipalities/Cities');
    }
}
