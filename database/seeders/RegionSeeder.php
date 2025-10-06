<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Region;
use Illuminate\Support\Facades\DB;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds CARAGA Region (Region XIII) with its provinces
     * CARAGA is located in the northeastern part of Mindanao
     */
    public function run(): void
    {
        DB::transaction(function () {
            // CARAGA Region XIII - Main Region
            $caraga = Region::create([
                'name' => 'CARAGA',
                'code' => 'XIII',
                'description' => 'CARAGA Administrative Region (Region XIII) - Covers the northeastern section of Mindanao including five provinces: Agusan del Norte, Agusan del Sur, Surigao del Norte, Surigao del Sur, and Dinagat Islands. Known for rich agricultural production including rice, corn, coconut, banana, cacao, coffee, and abaca. Major economic activities include agriculture, fishing, mining, and forestry.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // CARAGA Provinces
            $provinces = [
                [
                    'name' => 'Agusan del Norte',
                    'code' => 'AGN',
                    'description' => 'Province in CARAGA Region known for rice and corn production. Capital: Cabadbaran City. Major crops include rice, corn, coconut, and banana. Has significant mining and forestry industries.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Agusan del Sur',
                    'code' => 'AGS',
                    'description' => 'Landlocked province in CARAGA Region. Capital: Prosperidad. Major agricultural producer of rice, corn, banana, and coconut. Known for vast timber resources and rich mineral deposits.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Surigao del Norte',
                    'code' => 'SUN',
                    'description' => 'Coastal province in CARAGA Region. Capital: Surigao City. Major producer of coconut, rice, corn, and marine products. Known for mineral resources, particularly nickel and gold. Has significant fishing industry.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Surigao del Sur',
                    'code' => 'SUS',
                    'description' => 'Province in CARAGA Region known for cacao and coffee production. Capital: Tandag City. Major crops include cacao, coffee, coconut, rice, and corn. Has growing eco-tourism industry.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Dinagat Islands',
                    'code' => 'DIN',
                    'description' => 'Island province in CARAGA Region known for abaca and coconut production. Capital: San Jose. Major producer of abaca, coconut, rice, and marine resources. Rich in mineral deposits and marine biodiversity.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($provinces as $province) {
                Region::create($province);
            }
        });

        $this->command->info('CARAGA Region and provinces seeded successfully!');
    }
}
