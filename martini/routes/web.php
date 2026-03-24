<?php

use App\Http\Controllers\ActiveHolidayCoverController;
use App\Http\Controllers\BulkPermissionController;
use App\Http\Controllers\CustomerOverridesController;
use App\Http\Controllers\CutController;
use App\Http\Controllers\CutGroupController;
use App\Http\Controllers\CutGroupNationalityDateController;
use App\Http\Controllers\DeliveryCustomerController;
use App\Http\Controllers\DropdownController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\HealthMarkController;
use App\Http\Controllers\InboundContainerApprovalController;
use App\Http\Controllers\InboundContainerController;
use App\Http\Controllers\IntakeReportController;
use App\Http\Controllers\LegacyController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShortStockController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\StockMovementRuleController;
use App\Http\Controllers\OutgoingPalletController;
use App\Http\Controllers\OutgoingPalletsLoadingController;
use App\Http\Controllers\SupplierReturnAttachmentController;
use App\Http\Controllers\SupplierReturnController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserCustomerController;
use App\Http\Controllers\VehicleController;
use App\Models\ContainerProduct;
use App\Models\InboundContainer;
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

    Route::get('/vehicles/search', [VehicleController::class, 'search'])->name('vehicles.search');
    Route::resource('vehicles', 'App\Http\Controllers\VehicleController');

    Route::get('usercustomer',[UserCustomerController::class,'index'])->name('usercustomer.index');
    Route::get('/usercustomer/download', [UserCustomerController::class, 'download'])->name('usercustomer.download');

    Route::get('deliverycustomer',[DeliveryCustomerController::class,'index'])->name('deliverycustomer.index');
    Route::get('/deliverycustomer/download', [DeliveryCustomerController::class, 'download'])->name('deliverycustomer.download');

    Route::get('bulkpermissions', [BulkPermissionController::class, 'view'])->name('bulkpermission.view');
    Route::get('bulkpermissions/search', [BulkPermissionController::class, 'search'])->name('bulkpermission.search');
    Route::put('bulkpermissions/save', [BulkPermissionController::class, 'save'])->name('bulkpermission.save');

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

    Route::get('/sites/{site}/movement/create', [StockMovementRuleController::class, 'create'])->name('stockmovements.create');
    Route::get('/sites/movement/{stockmovementrule}/edit', [StockMovementRuleController::class, 'show'])->name('stockmovements.edit');
    Route::post('/sites/movement/store', [StockMovementRuleController::class, 'store'])->name('stockmovements.store');
    Route::put('/sites/movement/{stockmovementrule}', [StockMovementRuleController::class, 'update'])->name('stockmovements.update');

    Route::get('/shortstock', [ShortStockController::class, 'index'])->name('shortstock.index');
    Route::get('/shortstock/download', [ShortStockController::class, 'download'])->name('shortstock.download');

    Route::get('/outgoing-pallets', [OutgoingPalletController::class, 'index'])->name('outgoing-pallets.index');
    Route::get('/outgoing-pallets/details', [OutgoingPalletController::class, 'details'])->name('outgoing-pallets.details');
    Route::post('/outgoing-pallets', [OutgoingPalletController::class, 'createPallet'])->name('outgoing-pallets.create');
    Route::delete('/outgoing-pallets/{outgoingPallet}', [OutgoingPalletController::class, 'deletePallet'])->name('outgoing-pallets.delete');
    Route::post('/outgoing-pallets/attach-pick', [OutgoingPalletController::class, 'attachPick'])->name('outgoing-pallets.attach-pick');
    Route::post('/outgoing-pallets/detach-pick', [OutgoingPalletController::class, 'detachPick'])->name('outgoing-pallets.detach-pick');
    Route::post('/outgoing-pallets/update-type', [OutgoingPalletController::class, 'updatePalletType'])->name('outgoing-pallets.update-type');
    Route::post('/outgoing-pallets/split-pick', [OutgoingPalletController::class, 'splitPick'])->name('outgoing-pallets.split-pick');
    Route::post('/outgoing-pallets/render-pick-html', [OutgoingPalletController::class, 'renderPickHtml'])->name('outgoing-pallets.render-pick-html');
    Route::post('/outgoing-pallets/pick-pallets', [OutgoingPalletController::class, 'pickPallets'])->name('outgoing-pallets.pick-pallets');

    // Outgoing Pallets Loading endpoints
    Route::get('/outgoing-pallets-loading', [OutgoingPalletsLoadingController::class, 'view'])->name('outgoing-pallets-loading.view');
    Route::get('/outgoing-pallets-loading/vehicles', [OutgoingPalletsLoadingController::class, 'vehicle'])->name('outgoing-pallets-loading.vehicles');
    Route::get('/outgoing-pallets-loading/vehicle-details', [OutgoingPalletsLoadingController::class, 'vehicleDetails'])->name('outgoing-pallets-loading.vehicle-details');
    Route::get('/outgoing-pallets-loading/vehicle-allocations', [OutgoingPalletsLoadingController::class, 'vehicleAllocations'])->name('outgoing-pallets-loading.vehicle-allocations');
    Route::post('/outgoing-pallets-loading/update-allocation', [OutgoingPalletsLoadingController::class, 'updateAllocation'])->name('outgoing-pallets-loading.update-allocation');
    Route::post('/outgoing-pallets-loading/update-pallet-type', [OutgoingPalletsLoadingController::class, 'updatePalletType'])->name('outgoing-pallets-loading.update-pallet-type');
    Route::post('/outgoing-pallets-loading/commit-allocations', [OutgoingPalletsLoadingController::class, 'commitAllocations'])->name('outgoing-pallets-loading.commit-allocations');
    Route::get('/outgoing-pallets-loading/print-truck-load', [OutgoingPalletsLoadingController::class, 'printTruckLoad'])->name('outgoing-pallets-loading.print-truck-load');
    Route::get('/outgoing-pallets-loading/pallet-selection', [OutgoingPalletsLoadingController::class, 'palletSelection'])->name('outgoing-pallets-loading.pallet-selection');
    Route::get('/outgoing-pallets-loading/pallet-overview', [OutgoingPalletsLoadingController::class, 'palletOverview'])->name('outgoing-pallets-loading.pallet-overview');
    Route::get('/outgoing-pallets-loading/orders', [OutgoingPalletsLoadingController::class, 'orders'])->name('outgoing-pallets-loading.orders');
    Route::get('/outgoing-pallets-loading/depots', [OutgoingPalletsLoadingController::class, 'depots'])->name('outgoing-pallets-loading.depots');
    Route::post('/outgoing-pallets-loading/ai-plan', [OutgoingPalletsLoadingController::class, 'aiPlan'])->name('outgoing-pallets-loading.ai-plan');


    Route::get('containers/search', [InboundContainerController::class, 'search'])->name('containers.search');
    Route::get('containers/{existingContainer}/clone-container', [InboundContainerController::class, 'cloneContainer'])->name('containers.clone-container');
    Route::resource('containers', 'App\Http\Controllers\InboundContainerController');
    Route::get('/containers/{container}/predelete', [InboundContainerController::class, 'preDelete'])->name('containers.predelete');
    Route::delete('/containers/{container}/delete', [InboundContainerController::class, 'confirmDelete'])->name('containers.delete');

    Route::get('/containers/{container}/product/create', [InboundContainerController::class, 'createProduct'])->name('container-product.create');
    Route::post('/containers/{container}/product/store', [InboundContainerController::class, 'storeProduct'])->name('container-product.store');
    Route::get('/containers/{container}/arrived', [InboundContainerController::class, 'arrive'])->name('containers.arrive');
    Route::get('/containers/{container}/product/{containerProduct}/edit', [InboundContainerController::class, 'editProduct'])->name('container-product.edit');
    Route::put('/containers/{container}/product/{containerProduct}', [InboundContainerController::class, 'updateProduct'])->name('container-product.update');
    Route::get('/containers/{container}/product/{containerProduct}/predelete', [InboundContainerController::class, 'preDeleteProduct'])->name('container-product.predelete');
    Route::delete('/containers/{container}/product/{containerProduct}/delete', [InboundContainerController::class, 'confirmDeleteProduct'])->name('container-product.delete');
    Route::get('/containers/{container}/approvals/create', [InboundContainerApprovalController::class, 'create'])->name('inbound-approvals.create');
    Route::post('/containers/{container}/approvals', [InboundContainerApprovalController::class, 'store'])->name('inbound-approvals.store');
    Route::get('/containers/{container}/approvals/{approval}/destroy', [InboundContainerApprovalController::class, 'destroy'])->name('inbound-approvals.destroy');

    Route::get('/cutgroups/{speciesId}', [CutGroupController::class, 'getCutGroups']);
    Route::get('/cuts/{cutGroupId}', [CutController::class, 'getCuts']);

    Route::get('files/{file}/download', [FileController::class, 'download'])->name('files.download');
    Route::get('files/{file}/view', [FileController::class, 'view'])->name('files.view');

    Route::post('/supplier-return-attachments', [SupplierReturnAttachmentController::class, 'store'])->name("supplier-return-attachment.store");
    Route::post('/supplier-return-attachments/{supplierReturnAttachment}', [SupplierReturnAttachmentController::class, 'update'])->name("supplier-return-attachment.update");
    Route::delete('/supplier-return-attachments/{supplierReturnAttachment}', [SupplierReturnAttachmentController::class, 'destroy'])->name("supplier-return-attachment.destroy");

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
