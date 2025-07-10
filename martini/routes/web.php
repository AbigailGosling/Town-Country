<?php

use App\Http\Controllers\ActiveHolidayCoverController;
use App\Http\Controllers\CustomerOverridesController;
use App\Http\Controllers\CutGroupNationalityDateController;
use App\Http\Controllers\DropdownController;
use App\Http\Controllers\HealthMarkController;
use App\Http\Controllers\IntakeReportController;
use App\Http\Controllers\LegacyController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShortStockController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierReturnController;
use App\Http\Controllers\UserController;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

    Route::get('/sites/search', [SiteController::class, 'search'])->name('sites.search');

    Route::resource('sites', 'App\Http\Controllers\SiteController');
    Route::get('/sites/{site}/locations/create', [LocationController::class, 'create'])->name('locations.create');
    Route::post('/sites/{site}/locations/store', [LocationController::class, 'store'])->name('locations.store');
    Route::get('/sites/{site}/locations/{location}/edit', [LocationController::class, 'edit'])->name('locations.edit');
    Route::put('/sites/{site}/locations/{location}', [LocationController::class, 'update'])->name('locations.update');

    Route::resource('cutdates', 'App\Http\Controllers\CutGroupNationalityDateController');

    Route::get('deleteholiday/{holiday}', [ActiveHolidayCoverController::class, 'destroy'])->name('holidays.delete');
    Route::resource('holidays', 'App\Http\Controllers\ActiveHolidayCoverController');

    Route::post('/cutdates/search', [CutGroupNationalityDateController::class, 'search'])->name('cutdates.search');
    Route::get('api/fetch-cutgroups', [DropdownController::class, 'fetchCutGroups']);

    Route::get('/customers/overrides', [CustomerOverridesController::class, 'index'])->name('overrides.index');
    Route::get('/customers/overrides/search', [CustomerOverridesController::class, 'search'])->name('overrides.search');
    Route::get('/customers/overrides/edit/{customer}', [CustomerOverridesController::class, 'edit'])->name('overrides.edit');
    Route::post('/customers/overrides/update_credit/{customer}', [CustomerOverridesController::class, 'updateCredit'])->name('overrides.update_credit');
    Route::post('/customers/overrides/update_del/{customer}', [CustomerOverridesController::class, 'updateDel'])->name('overrides.update_del');

    Route::get('/report/{report}', [ReportController::class, 'show'])->name('report.show');
    Route::post('/report/{report}', [ReportController::class, 'show'])->name('report.show');

    Route::get('/intake_report', [IntakeReportController::class, 'show'])->name('intake_report.show');
    Route::post('/intake_report', [IntakeReportController::class, 'show'])->name('intake_report.show');


    Route::get('health_marks/search', [HealthMarkController::class, 'search'])->name('health_marks.search');
    Route::resource('health_marks', 'App\Http\Controllers\HealthMarkController');

    Route::get('supplierreturnstatements', [SupplierReturnController::class, 'index'])->name('supplierreturnstatements.index');
    Route::get('supplierreturnstatements/{supplier}', [SupplierReturnController::class, 'show'])->name('supplierreturnstatements.show');

    Route::get('/sites/{site}/movement/create', [StockMovementController::class, 'create'])->name('stockmovements.create');
    Route::get('/sites/movement/{stockmovement}/edit', [StockMovementController::class, 'show'])->name('stockmovements.edit');
    Route::post('/sites/movement/store', [StockMovementController::class, 'store'])->name('stockmovements.store');
    Route::put('/sites/movement/{stockmovement}', [StockMovementController::class, 'update'])->name('stockmovements.update');

    Route::get('/shortstock', [ShortStockController::class, 'index'])->name('shortstock.index');
    Route::get('/shortstock/download', [ShortStockController::class, 'download'])->name('shortstock.download');
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
Route::post('legacy/scripts/SLabsNotifier.php', [LegacyController::class,'entry_point'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class,'auth', 'verified']);
Route::any('legacy/images/{path}', [LegacyController::class,'entry_point'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class,'auth', 'verified'])->where('path', '.*');
Route::any('legacy/css/{path}', [LegacyController::class,'entry_point'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class,'auth', 'verified'])->where('path', '.*');
Route::any('legacy/fonts/{path}', [LegacyController::class,'entry_point'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class,'auth', 'verified'])->where('path', '.*');
Route::any('legacy/js/{path}', [LegacyController::class,'entry_point'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class,'auth', 'verified'])->where('path', '.*');
Route::any('legacy/img/{path}', [LegacyController::class,'entry_point'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class,'auth', 'verified'])->where('path', '.*');
Route::any('/{path}', [LegacyController::class,'entry_point'])->name('legacy')->middleware(['auth', 'verified'])->where('path', '.*');
