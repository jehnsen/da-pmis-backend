<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ResidentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('en_PH'); // Set to Philippine locale

        for ($i = 0; $i < 800; $i++) {
            DB::table('residents')->insert([
                'firstname' => $faker->firstName,
                'middlename' => $faker->randomElement(['', $faker->lastName]),
                'lastname' => $faker->lastName,
                'birthdate' => $faker->date('Y-m-d', '2003-01-01'),
                'birthplace' => $faker->city,
                'gender' => $faker->randomElement(['Male', 'Female']),
                'purok_no' => $faker->numberBetween(1, 10),
                'civil_status' => $faker->randomElement(['Single', 'Married', 'Widowed', 'Separated']),
                'nationality' => 'Filipino',
                'religion_id' => $faker->numberBetween(1, 10), // Assuming you have 10 religion types in the table
                'contact_no' => $faker->randomElement([$faker->phoneNumber, null]),
                'email' => $faker->optional()->email,
                'household_id' => null, //$faker->numberBetween(100, 500),
                'spouse_id' => null,  // Always set spouse_id to null
                'spouse_name' => null, // Since there's no spouse, set spouse_name to null
                'educ_attainment' => $faker->randomElement(['Elementary', 'High School', 'College', 'Post-Graduate', '']),
                'occupation' => $faker->randomElement(['Farmer', 'Vendor', 'Teacher', 'Unemployed', 'Fisherman', 'Construction Worker','Jeepney Driver', 'Tricycle Driver','Government Employee', '']),
                'is_four_ps' => $faker->randomElement([0, 1]),
                'residency_length' => $faker->numberBetween(1, 50), // Residency in years
                'resident_type' => $faker->randomElement([1, 2, 3, 4, 5, 6 ,7, 8]), // Random resident type
                'is_registered_voter' => $faker->boolean(80), // 80% chance of being a registered voter
                'precint_no' => $faker->optional()->numerify('######'), // Optional precinct number
                'status' => $faker->randomElement([1, 0, 2]), // Active, deceased, or transferred
                'tags' => $faker->randomElement([1, 2, 3]), // Custom tagging system
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
