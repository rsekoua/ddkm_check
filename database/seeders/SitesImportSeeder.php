<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Pres;
use App\Models\Region;
use App\Models\Site;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SitesImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Chemin corrigé
        $filePath = Storage::path('sites.xlsx');

        $this->command->info("Recherche du fichier : {$filePath}");

        if (!file_exists($filePath)) {
            $this->command->error("Le fichier sites.xlsx n'existe pas à l'emplacement : {$filePath}");

            // Lister les fichiers dans le répertoire storage pour déboguer
            $files = glob(storage_path('app') . '/*');
            $this->command->info("Fichiers trouvés dans storage/app/ :");
            foreach ($files as $file) {
                $this->command->info(" - " . basename($file));
            }

            if (is_dir(storage_path('app/private'))) {
                $files = glob(storage_path('app/private') . '/*');
                $this->command->info("Fichiers trouvés dans storage/app/private/ :");
                foreach ($files as $file) {
                    $this->command->info(" - " . basename($file));
                }
            } else {
                $this->command->error("Le dossier private n'existe pas!");
            }

            return;
        }

        $this->command->info("Fichier trouvé, chargement en cours...");

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            $this->command->info("Fichier Excel chargé avec succès.");

            $presMap = [];
            $regionsMap = [];
            $districtsMap = [];
            $row = 2;
            $importCount = 0;

            $columnLetters = ['A', 'B', 'C', 'D']; // A=PRES, B=Région, C=District, D=Site

            // Vérifier la première cellule pour s'assurer que le fichier contient des données
            $firstCell = $worksheet->getCell($columnLetters[0] . '2')->getValue();
            $this->command->info("Première cellule (A2) contient : " . ($firstCell ?: 'VIDE'));

            while ($worksheet->getCell($columnLetters[0] . $row)->getValue() !== null) {
                $presName = $worksheet->getCell($columnLetters[0] . $row)->getValue();
                $regionName = $worksheet->getCell($columnLetters[1] . $row)->getValue();
                $districtName = $worksheet->getCell($columnLetters[2] . $row)->getValue();
                $siteName = $worksheet->getCell($columnLetters[3] . $row)->getValue();

               // $this->command->info("Ligne {$row}: PRES: {$presName}, Région: {$regionName}, District: {$districtName}, Site: {$siteName}");

                // Créer ou récupérer le PRES
                if (!isset($presMap[$presName])) {
                    // Vérifiez si vous avez un modèle Pres, sinon créez-le
                    if (class_exists('\App\Models\Pres')) {
                        $pres = Pres::query()->firstOrCreate([
                            'name' => $presName,
                            'slug' => Str::slug($presName)
                        ]);
                        $presMap[$presName] = $pres->id;
                        $this->command->info("PRES créé/trouvé: {$presName} (ID: {$pres->id})");
                    } else {
                        $this->command->warning("Le modèle Pres n'existe pas. Ignoré pour l'importation.");
                        // Utiliser une valeur factice pour éviter les erreurs
                        $presMap[$presName] = 0;
                    }
                }

                // Créer ou récupérer la région
                $regionKey = $presName . '-' . $regionName;
                if (!isset($regionsMap[$regionKey])) {
                    $regionData = [
                        'name' => $regionName,
                        'slug' => Str::slug($regionName)
                    ];

                    // Ajouter le pres_id seulement si le modèle existe
                    if (class_exists('\App\Models\Pres') && isset($presMap[$presName]) && $presMap[$presName] > 0) {
                        $regionData['pres_id'] = $presMap[$presName];
                    }

                    $region = Region::query()->firstOrCreate($regionData);
                    $regionsMap[$regionKey] = $region->id;
                    $this->command->info("Région créée/trouvée: {$regionName} (ID: {$region->id})");
                }

                // Créer ou récupérer le district
                $districtKey = $regionKey . '-' . $districtName;
                if (!isset($districtsMap[$districtKey])) {
                    $district = District::query()->firstOrCreate([
                        'name' => $districtName,
                        'slug' => Str::slug($districtName),
                        'region_id' => $regionsMap[$regionKey]
                    ]);
                    $districtsMap[$districtKey] = $district->id;
                  //  $this->command->info("District créé/trouvé: {$districtName} (ID: {$district->id})");
                }

                // Créer le site
                $site = Site::query()->firstOrCreate([
                    'name' => $siteName,
                    'slug' => Str::slug($siteName),
                    'district_id' => $districtsMap[$districtKey]
                ]);
               // $this->command->info("Site créé/trouvé: {$siteName} (ID: {$site->id})");

                $importCount++;
                $row++;
            }

            $this->command->info("Importation terminée! {$importCount} enregistrements traités.");
        } catch (\Exception $e) {
            $this->command->error("Erreur lors de l'importation: " . $e->getMessage());
            $this->command->error("Trace: " . $e->getTraceAsString());
        }
    }
}
