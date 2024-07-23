<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lobby extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'is_private',
        'gamemode',
        'max_players',
        'status',
        'password',
        'player_id_has_turn',
        'drawable_word',
        'drawing_path',
        'current_round',
        'rounds',
        'random_image'
    ];

    protected $casts = [
        'password' => 'hashed'
    ];

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function playerHaveHadTurn(): HasMany
    {
        return $this->hasMany(PlayerHaveHadTurn::class);
    }
}
