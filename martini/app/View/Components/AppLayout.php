<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AppLayout extends Component
{
    public bool $expand = false;
    public bool $hideLinks = false;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(bool $expand = false,bool $hideLinks = false)
    {
        $this->expand = $expand;
        $this->hideLinks = $hideLinks;
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
