<?php

namespace Database\Seeders;

use App\Models\Lobby;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LobbySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 10; $i++) {
            Lobby::create([
                'slug' => fake()->unique()->slug,
                'name' => fake()->word,
                'password' => fake()->optional()->password,
                'max_players' => fake()->numberBetween(2, 10),
                'is_private' => fake()->boolean,
                'status' => fake()->randomElement(['in_lobby', 'in_game', 'finished']),
                'gamemode' => fake()->randomElement(['freehand', 'retrace']),
            ]);
        }
    }
}
