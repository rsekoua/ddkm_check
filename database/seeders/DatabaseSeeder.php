<?php

namespace Database\Seeders;

use App\Models\DeliveryType;
use App\Models\District;
use App\Models\Region;
use App\Models\Site;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

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

        $filePath = Storage::path('app/private/sites.xlsx');

        if (!file_exists($filePath)) {
            Log::error("Le fichier sites.xlsx n'existe pas dans le dossier storage/app/private");
            return;
        }

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $regionsMap = [];
        $districtsMap = [];
        $row = 2;

        $columnLetters = ['A', 'B', 'C', 'D']; // A=1, B=2, C=3, etc.

        while ($worksheet->getCell($columnLetters[0] . $row)->getValue() !== null) {
            $regionName = $worksheet->getCell($columnLetters[0] . $row)->getValue();
            $districtName = $worksheet->getCell($columnLetters[1] . $row)->getValue();
            $siteName = $worksheet->getCell($columnLetters[2] . $row)->getValue();

            // Créer ou récupérer la région
            if (!isset($regionsMap[$regionName])) {
                $region = Region::query()->firstOrCreate([
                    'name' => $regionName,
                    'slug' => Str::slug($regionName)
                ]);
                $regionsMap[$regionName] = $region->id;
            }

            // Créer ou récupérer le district
            $districtKey = $regionName . '-' . $districtName;
            if (!isset($districtsMap[$districtKey])) {
                $district = District::query()->firstOrCreate([
                    'name' => $districtName,
                    'slug' => Str::slug($districtName),
                    'region_id' => $regionsMap[$regionName]
                ]);
                $districtsMap[$districtKey] = $district->id;
            }

            // Créer le site
            Site::query()->firstOrCreate([
                'name' => $siteName,
                'slug' => Str::slug($siteName),
                'district_id' => $districtsMap[$districtKey]
            ]);

            $row++;
        }

        $this->command->info('Importation des données depuis sites.xlsx terminée avec succès.');

    }


}
