<?php
use App\Http\Controllers\LegacyController;
use App\Http\Controllers\UserController;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->intended('/login');
});

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {
        return redirect()->intended(RouteServiceProvider::HOME);
    })->name('dashboard');

    Route::any('/togglesuperadmin', function () {
        $user = User::find(Auth::user()->id);
        if ($user->hasPermission("superadmin"))
        {
            $user->toggleSuperAdminMode();
            return redirect()->intended(RouteServiceProvider::HOME);
        }
        else
        {
            abort(404);
        }
    });

    Route::resource('users', 'App\Http\Controllers\UserController');
});

//THIS MUST BE LAST!
Route::any('/{path}', [LegacyController::class,'entry_point'])->middleware(['auth', 'verified'])->where('path', '.*');
