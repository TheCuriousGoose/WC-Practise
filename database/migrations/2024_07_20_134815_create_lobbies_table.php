<?php

use App\Enums\Gamemode;
use App\Enums\Status;
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
        Schema::create('lobbies', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('password')->nullable();
            $table->integer('max_players');
            $table->boolean('is_private')->default(false);
            $table->enum('status', [Status::IN_LOBBY->value, Status::IN_GAME->value, Status::FINISHED->value]);
            $table->enum('gamemode', [Gamemode::FREEHAND->value(), Gamemode::RETRACE->value()]);;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lobbies');
    }
};
