<?php

namespace Database\Seeders;

use App\Models\Martyr;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class MartyrSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Predefined death reasons from MartyrActReasons
        $deathReasons = [
            'Police Brutality',
            'Democratic Rights',
            'Heroic Acts'
        ];

        for ($i = 0; $i < 1000; $i++) {
            $name = $faker->name;
            $death_date = $faker->dateTimeBetween('-50 years', 'now');
            $birth_date = $faker->dateTimeBetween('-80 years', $death_date);

            Martyr::create([
                'email' => $faker->unique()->safeEmail,
                'name' => $name,
                'birth_date' => $birth_date->format('Y-m-d'),
                'death_date' => $death_date->format('Y-m-d'),
                'location' => $faker->country,
                'contributions' => $faker->paragraph,
                'death_reason' => $faker->randomElement($deathReasons), // Randomly select a predefined death reason
                'profile_picture' => $faker->imageUrl(640, 480, 'people', true, 'martyr'),
                'is_active' => $faker->boolean(true),
            ]);
        }
    }
}
