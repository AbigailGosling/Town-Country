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
    Route::get('/users/search', [UserController::class, 'search'])->name('users.search');
    Route::get('/users/forgottenPassword', [UserController::class, 'resetPassword'])->name('users.forgot-password');
    Route::resource('users', 'App\Http\Controllers\UserController');

});
Route::get('/menu.php', function () {
    return redirect('/legacy/menu.php');
});
Route::get('/logout.php', function () {
    return redirect('/logout');
});
Route::get('legacy/logout.php', function () {
    return redirect('/logout');
});
Route::get('legacy/logout', function () {
    return redirect('/logout');
});
//THIS MUST BE LAST!
Route::any('/{path}', [LegacyController::class,'entry_point'])->middleware(['auth', 'verified'])->where('path', '.*');
