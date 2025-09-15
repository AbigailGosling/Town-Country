<?php

use App\Models\Customer;
use App\Models\InboundContainer;
use App\Models\Location;
use App\Models\Site;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;
use Illuminate\Http\Request;
use App\Models\User;

Breadcrumbs::for('home', function(BreadcrumbTrail $trail){
   $trail->push('Home', route('dashboard'));
});

Breadcrumbs::for('users.index', function(BreadcrumbTrail $trail){
    $trail->push('Home', route('dashboard'));
    $trail->push('Users');
});

Breadcrumbs::for('users.search', function(BreadcrumbTrail $trail){
    $trail->push('Home', route('dashboard'));
    $trail->push('Users', route('users.index'));
    if($search = request()->input('search')) {
        $trail->push($search);
    }
});

Breadcrumbs::for('users.edit', function(BreadcrumbTrail $trail, User $user){
    $trail->push('Home', route('dashboard'));
    $trail->push('Users', route('users.index'));
    $trail->push($user->name);
});

Breadcrumbs::for('overrides.index', function(BreadcrumbTrail $trail){
    $trail->push('Home', route('dashboard'));
    $trail->push('Overrides');
});

Breadcrumbs::for('overrides.search', function(BreadcrumbTrail $trail){
    $trail->push('Home', route('dashboard'));
    $trail->push('Overrides', route('overrides.index'));
    if($search = request()->input('search')) {
        $trail->push($search);
    }
});

Breadcrumbs::for('overrides.edit', function(BreadcrumbTrail $trail, Customer $user){
    $trail->push('Home', route('dashboard'));
    $trail->push('Overrides', route('overrides.index'));
    $trail->push($user->businessname);
});

Breadcrumbs::for('sites.index', function(BreadcrumbTrail $trail){
    $trail->push('Home', route('dashboard'));
    $trail->push('Sites');
});

Breadcrumbs::for('sites.search', function(BreadcrumbTrail $trail){
    $trail->push('Home', route('dashboard'));
    $trail->push('Sites', route('sites.index'));
    if($search = request()->input('search')) {
        $trail->push($search);
    }
});

Breadcrumbs::for('sites.edit', function(BreadcrumbTrail $trail, Site $user){
    $trail->push('Home', route('dashboard'));
    $trail->push('Sites', route('sites.index'));
    $trail->push($user->name);
});

Breadcrumbs::for('locations.edit', function(BreadcrumbTrail $trail, Site $site, Location $user){
    $trail->push('Home', route('dashboard'));
    $trail->push('Sites', route('sites.index'));
    $trail->push($site->name,route('sites.edit',$site->id));
    $trail->push($user->name);
});

Breadcrumbs::for('containers.index', function(BreadcrumbTrail $trail){
    $trail->push('Home', route('dashboard'));
    $trail->push('Containers');
});
Breadcrumbs::for('containers.edit', function(BreadcrumbTrail $trail, InboundContainer $container){
    $trail->push('Home', route('dashboard'));
    $trail->push('Containers', route('containers.index'));
    $trail->push($container->internal_number);
});
Breadcrumbs::for('inbound-approvals.create', function(BreadcrumbTrail $trail, InboundContainer $container){
    $trail->push('Home', route('dashboard'));
    $trail->push('Containers', route('containers.index'));
    $trail->push($container->internal_number,route('containers.edit',$container->id));
    $trail->push("Documents and Approval");
});
