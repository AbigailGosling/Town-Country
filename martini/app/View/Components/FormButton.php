<?php

namespace App\View\Components;

use Illuminate\View\Component;

class FormButton extends Component
{
    public bool $submit;
    public bool $disable;
    public string $id;
    public string $title;
    public string $iconClass;
    public string $route;
    public string $params;
    public string $background;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(string $title, string $iconClass, string $route = '', $params = '', $background = 'green', bool $submit = false, bool $disable = false, string $id = '')
    {
        $this->id = $id;
        $this->submit = $submit;
        $this->disable = $disable;
        $this->title = $title;
        $this->iconClass = $iconClass;
        $this->route = $route;
        $this->params = $params;
        $this->background = $background;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.form-button');
    }
}
