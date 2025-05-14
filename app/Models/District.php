<?php

namespace App\Models;

use Filament\Models\Contracts\HasCurrentTenantLabel;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class District extends Model implements HasName,HasCurrentTenantLabel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'region_id',
        'name',
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
            'region_id' => 'integer',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
    public function getFilamentName(): string
    {
        return "District  {$this->name}";
    }
    public function getCurrentTenantLabel(): string
    {
        return "Region  {$this->region()->first()->name}";
    }

    /**
     * Set the name and automatically generate the slug.
     *
     * @param  string  $value
     * @return void
     */
//    public function setNameAttribute($value)
//    {
//        $this->attributes['name'] = $value;
//        $this->attributes['slug'] = Str::slug($value); // Génère le slug à partir du nom
//    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        if ($value) { // S'assurer que la valeur n'est pas vide pour générer le slug
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    public function getRouteKeyName()
    {
        return 'slug'; // Indique à Laravel d'utiliser la colonne 'slug' pour le binding
    }


}
