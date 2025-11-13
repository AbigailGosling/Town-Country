<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Jenssegers\Agent\Agent;

class DataTable extends Component
{
    public $agent;
    public string $headerColour;
    public string $footerColour;
    public bool $fixed;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($headerColour = "gray-200",$footerColour = "gray-200",$fixed = false)
    {
        $this->headerColour = $headerColour;
        $this->footerColour = $footerColour;
        $this->agent = new Agent();
        $this->fixed = $fixed;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.data-table');
    }
}
