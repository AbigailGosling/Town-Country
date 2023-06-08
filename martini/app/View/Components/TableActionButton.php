<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

class TableActionButton extends Component
{
    //The Laravel route name and the ID to pass through in the request here
    public string $route;
    public $id;
    public $type;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(string $route = '', string $type = 'success', $id = 0)
    {
        $this->route = $route;
        $this->id = $id;
        $this->type = $type;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.table-action-button');
    }
}
