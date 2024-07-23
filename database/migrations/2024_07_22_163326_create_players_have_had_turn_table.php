<?php

use App\Models\Lobby;
use App\Models\Player;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('player_have_had_turns', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Lobby::class);
            $table->foreignIdFor(Player::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players_have_had_turn');
    }
};
