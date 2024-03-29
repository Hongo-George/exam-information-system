<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StreamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word(),
            'alias' => $this->faker->unique()->word(),
            'description' => $this->faker->paragraph()
        ];
    }
}
