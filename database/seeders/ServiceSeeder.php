<?php

namespace Database\Seeders;
use App\Models\CarsService;
use App\Models\CarsOwner;
use App\Models\Worker;
use App\Models\Cars;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $idsCars = Cars::all()->pluck('id')->toArray();
        $idsOwners = CarsOwner::all()->pluck('id')->toArray();
        $idsWorkers = Worker::all()->pluck('id')->toArray();
        $faker = \Faker\Factory::create();
        for ($i = 0; $i < 5; $i++) 
        {
            CarsService::insert([
                "name_service" =>json_encode($faker->randomElement([
                    'Troca de óleo',
                    'Alinhamento e balanceamento',
                    'Troca de filtro de ar',
                    'Troca de filtro de óleo',
                    'Revisão dos freios',
                    'Troca de velas',
                    'Verificação e ajuste de correias',
                    'Limpeza do sistema de injeção',
                    'Troca de fluido de freio',
                    'Verificação de nível de líquidos',
                    'Verificação de sistema elétrico',
                    'Balanceamento de rodas',
                    'Troca de pneus',
                    'Alinhamento de direção',
                    'Revisão de suspensão',
                    'Troca de fluido de transmissão'
                  ])),
                "price" => $faker->numberBetween(1500, 5000),
                "car_id" => $faker->randomElement($idsCars),
                "owner_id" => $faker->randomElement($idsOwners ),
                "worker_id"=>$faker->randomElement( $idsWorkers),
                "date_service" => $faker->dateTime(),
                "created_at"=>\Carbon\Carbon::now()
            ]);
        }
    }
}
