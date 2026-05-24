<?php

namespace Database\Factories;

use App\Models\Festival;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Festival>
 */
class FestivalFactory extends Factory
{
    /**
     * Define the model's default state
     * 
     *
     * @return array<string, mixed>
     */
    protected $model = Festival::class;
    public function definition(): array
    {
         $festivalNames = ['Spring Festival', 'Summer Sale', 'Autumn Harvest', 'Winter Wonderland', 'New Year Bash'];
        return [
            //
          
                'name' => $this->faker->randomElement($festivalNames),
                'discount' => $this->faker->numberBetween(10, 50),
                'status' => 1,
                'start_date' => $this->faker->date(),
                'end_date' => $this->faker->date(),
          
        ];
    }
}
