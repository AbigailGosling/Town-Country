<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

class TableActionButton extends Component
{
    //The Laravel route name and the ID to pass through in the request here
    public string $route;
    public $id;
    public string $type;
    public string $extras;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(string $route = '', string $type = 'success', $id = 0,string $extras = '')
    {
        $this->route = $route;
        $this->id = $id;
        $this->type = $type;
        $this->extras = $extras;
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
