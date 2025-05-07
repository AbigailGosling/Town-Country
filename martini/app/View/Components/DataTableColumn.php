<?php

namespace App\View\Components;

use Illuminate\View\Component;

class DataTableColumn extends Component
{
    public $showOnMobile;
    public $align;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(bool $showOnMobile = true, String $align = "left")
    {
        $this->showOnMobile = $showOnMobile;
        $this->align = $align;
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
