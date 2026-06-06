<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Stat;
use App\Models\Invention;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventionStat>
 */
class InventionStatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        // inventos y stats
        $inventions = Invention::all();
        $invention = $inventions->isNotEmpty() ? $inventions->random() : null;


        $stats = Stat::all();
        $stat = $stats->isNotEmpty() ? $stats->random() : null;

    
        if (!$invention || !$stat) {
            return [];
        }

        return [
            'invention_id' => $invention->id,
            'stat_id' => $stat->id,
            'value' => rand(1, 10),
        ];
    }
}
