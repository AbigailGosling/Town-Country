<?php

namespace App\View\Components;

use Illuminate\View\Component;

class DataTableHeader extends Component
{
    public $showOnMobile;
    public $width;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(bool $showOnMobile = true,string $width = '100:px')
    {
        $this->showOnMobile = $showOnMobile;
        $this->width = $width;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.data-table-header');
    }
}
