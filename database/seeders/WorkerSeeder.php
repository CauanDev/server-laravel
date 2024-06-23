<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Worker;
class WorkerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        for ($i = 0; $i < 5; $i++) 
        {
            Worker::insert([
                "name" => $faker->name(),
                "email" => $faker->safeEmail,
                "salary" => $faker->numberBetween(1500, 5000),
                "age" => $faker->numberBetween(25, 50),
                "sex" => $faker->randomElement(["M", "F"]),
                "adress" => $faker->address,
                "created_at"=>\Carbon\Carbon::now()
            ]);
        }
    }
}
