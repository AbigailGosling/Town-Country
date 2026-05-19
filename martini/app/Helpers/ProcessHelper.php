<?php

namespace App\Helpers;

class ProcessHelper
{
    public static function runInBackground(string $args): void
    {
        $artisanLocation = base_path('artisan');
        pclose(popen('start /B cmd /C "php '.$artisanLocation.' '.$args.' >NUL 2>NUL"', 'r'));
    }
}
