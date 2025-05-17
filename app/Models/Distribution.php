<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class Distribution extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'district_id',
        'site_id',
        'delivery_type_id',
        'delivery_date',
        'difficulties',
        'solutions',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'district_id' => 'integer',
            'site_id' => 'integer',
            'delivery_type_id' => 'integer',
            'delivery_date' => 'date',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function deliveryType(): BelongsTo
    {
        return $this->belongsTo(DeliveryType::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

//    protected static function boot(): void
//    {
//        parent::boot();
//
//        // Nous pouvons conserver l'événement pour la validation côté serveur
//        // au cas où quelqu'un essaierait de contourner la validation du formulaire
//        static::saving(function (Distribution $distribution) {
//            // Ne pas vérifier si les données essentielles sont manquantes
//            if (!$distribution->site_id || !$distribution->delivery_type_id || !$distribution->delivery_date) {
//                return;
//            }
//
//            // Construire la requête pour vérifier les distributions existantes
//            $query = static::query()
//                ->whereMonth('delivery_date', $distribution->delivery_date->month)
//                ->whereYear('delivery_date', $distribution->delivery_date->year)
//                ->where('site_id', $distribution->site_id)
//                ->where('delivery_type_id', $distribution->delivery_type_id);
//
//            // Exclure l'enregistrement actuel en cas de mise à jour
//            if ($distribution->exists) {
//                $query->where('id', '!=', $distribution->id);
//            }
//
//            // Vérifier si une distribution existe déjà pour ce mois/site/type
//            if ($query->exists()) {
//                throw ValidationException::withMessages([
//                    'delivery_date' => 'Une distribution existe déjà pour ce site et ce type de livraison dans ce mois.'
//                ]);
//            }
//        });
//    }


}
