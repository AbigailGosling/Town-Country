<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AppLayout extends Component
{
    public bool $expand = false;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(bool $expand = false)
    {
        $this->expand = $expand;
    }
    /**
     * Get the view / contents that represents the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('layouts.app');
    }
}
