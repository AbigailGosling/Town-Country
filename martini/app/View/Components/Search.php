<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Search extends Component
{
    public string $search_term;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(string $search_term = '')
    {
        $this->search_term = $search_term;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.search');
    }
}
