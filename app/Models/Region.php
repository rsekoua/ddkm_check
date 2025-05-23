<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Region extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pres_id',
        'name',
        'slug'
    ];


    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        if ($value) { // S'assurer que la valeur n'est pas vide pour générer le slug
            $this->attributes['slug'] = Str::slug($value);
        }
    }

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

    public function pres(): BelongsTo
    {
        return $this->belongsTo(Pres::class);
    }
    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }
}
