<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AdminLayout extends Component
{
    public string $title;

    public function __construct(string $title = 'Admin')
    {
        $this->title = $title;
    }

    public function render(): View
    {
        return view('components.admin-layout');
    }
}