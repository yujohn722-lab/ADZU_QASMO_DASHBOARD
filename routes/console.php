<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('dashboard:about', function () {
    $this->info('Energy Crisis Learning Continuity Dashboard');
})->purpose('Display dashboard project information.');
