<?php

namespace App\View\Components\GE;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CheckBox extends Component
{
    public $row;
    /**
     * Create a new component instance.
     */
    public function __construct($row)
    {
        $this->row = $row;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.ge.check-box',['row' => $this->row]);
    }
}
