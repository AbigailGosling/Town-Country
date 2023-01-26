<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;
use App\Models\User;

Breadcrumbs::for('home', function(BreadcrumbTrail $trail){
   $trail->push('Home', route('dashboard'));
});

Breadcrumbs::for('users.index', function(BreadcrumbTrail $trail){
    $trail->push('Home', route('dashboard'));
    $trail->push('Users');
});

Breadcrumbs::for('users.edit', function(BreadcrumbTrail $trail, User $user){
    $trail->push('Home', route('dashboard'));
    $trail->push('Users', route('users.index'));
    $trail->push($user->name);
});

