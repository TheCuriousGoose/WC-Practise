<?php

namespace App\View\Components;

use App\Models\Lobby;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UsernameModal extends Component
{
    /**
     * Create a new component instance.
     */
    public $lobby;

    public function __construct(Lobby $lobby)
    {
        $this->lobby = $lobby;
    }


    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.username-modal');
    }
}
