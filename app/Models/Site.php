<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Site extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'district_id',
        'name',
        'address',
        'contact_info',
        'slug'
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
        ];
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
    }


    public function getRouteKeyName()
    {
        return 'slug'; // Remplacez 'slug' par le nom de votre colonne de slug dans la table des sites
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        if ($value) { // S'assurer que la valeur n'est pas vide pour générer le slug
            $this->attributes['slug'] = Str::slug($value);
        }
    }
}
