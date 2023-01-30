<?php

namespace App\View\Components;

use Illuminate\View\Component;

class DataTableColumn extends Component
{
    public $showOnMobile;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(bool $showOnMobile = true)
    {
        $this->showOnMobile = $showOnMobile;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.data-table-column');
    }
}
