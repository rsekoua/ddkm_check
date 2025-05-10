<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\DeliveryType;
use App\Models\Distribution;
use App\Models\District;
use App\Models\Site;

class DistributionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Distribution::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'district_id' => District::factory(),
            'site_id' => Site::factory(),
            'delivery_type_id' => DeliveryType::factory(),
            'delivery_date' => fake()->dateTime(),
            'difficulties' => fake()->text(),
            'solutions' => fake()->text(),
            'notes' => fake()->text(),
        ];
    }
}
