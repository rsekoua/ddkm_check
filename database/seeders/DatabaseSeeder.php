<?php

namespace Database\Seeders;

use App\Models\DeliveryType;
use App\Models\District;
use App\Models\Region;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'rsekoua',
            'email' => 'rsekoua@local.host',
            'password' => bcrypt('password'),
        ]);


//        // Liste des régions à créer
//        $regions = [
//            'BAFING',
//            'IFFOU',
//
//        ];
//        // Création des régions
//        foreach ($regions as $regionName) {
//            Region:: query()->create([
//                'name' => $regionName,
//            ]);
//        }

        // Création des districts pour chaque région
//        $districtsData = [
//            'BAFING' => ['KORO','OUANINOU','TOUBA'],
//            'IFFOU'=> ['DAOUKRO','M\'BAHIAKRO','PRIKRO'],
//        ];

//        foreach ($districtsData as $regionName => $districts) {
//            $region = Region::query()->where('name', $regionName)->first();
//
//            if ($region) {
//                foreach ($districts as $districtName) {
//                    District::query()->create([
//                        'region_id' => $region->id,
//                        'name' => $districtName,
//                    ]);
//                }
//            }
//        }
//
//
//        // Liste des régions à créer
//        $delivery_type  = [
//            'Par le district',
//            'Par la NPSP',
//            'Par l\'ESPC',
//            'Par un autre moyen',
//        ];
        // Création des régions
//        foreach ($delivery_type as $delivery_type_name) {
//            DeliveryType::query()->create([
//                'name' => $delivery_type_name,
//            ]);
//        }


    }
}
