<?php

namespace App\Enums;

enum Status: string
{
    case IN_LOBBY = 'in_lobby';
    case IN_GAME = 'in_game';
    case FINISHED = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::IN_LOBBY => 'In Lobby',
            self::IN_GAME => 'In Game',
            self::FINISHED => 'Finished',
        };
    }
}
