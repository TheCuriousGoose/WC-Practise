<?php

namespace Database\Seeders;

use App\Models\Lobby;
use App\Models\Player;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($i = 0; $i < 10; $i++){
            Player::create([
                'lobby_id' => Lobby::inRandomOrder()->first()->id,
                'name' => fake()->name(),
            ]);
        }
    }
}
