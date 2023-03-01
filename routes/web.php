<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\IndustryController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MethodController;
use App\Http\Controllers\TimezoneController;
use App\Http\Controllers\RoleGroupController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ScheduleGroupController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaxController;

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

Route::redirect('/', 'dashboard');

Route::get('test-api', function() {
    return ['name' => 'Azeem Ullah'];
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    
    Route::get('change-password', [UserController::class, 'changePassword'])->name('change-password');
    Route::post('change-password', [UserController::class, 'updatePassword'])->name('users.change-password');
    
    Route::resource('users', UserController::class);
    Route::post('users/ajax', [UserController::class, 'index'])->name('users.ajax');
    
    Route::resource('customers', CustomerController::class);
    Route::post('customers/ajax', [CustomerController::class, 'index'])->name('customers.ajax');
    
    Route::resource('companies', CompanyController::class);
    Route::post('companies/ajax', [CompanyController::class, 'index'])->name('companies.ajax');

    Route::resource('schedule-groups', ScheduleGroupController::class);
    Route::post('schedule-groups/ajax', [ScheduleGroupController::class, 'index'])->name('schedule-groups.ajax');
    Route::resource('schedules', ScheduleController::class);
    Route::post('schedules/ajax', [ScheduleController::class, 'index'])->name('schedules.ajax');

    Route::resource('items', ItemController::class);
    Route::post('items/ajax', [ItemController::class, 'index'])->name('items.ajax');
 
    Route::resource('taxes', TaxController::class);
    Route::post('taxes/ajax', [TaxController::class, 'index'])->name('taxes.ajax');
    
    Route::resource('methods', MethodController::class);
    Route::post('methods/ajax', [MethodController::class, 'index'])->name('methods.ajax');

    Route::resource('sources', SourceController::class);
    Route::post('sources/ajax', [SourceController::class, 'index'])->name('sources.ajax');

    Route::resource('services', ServiceController::class)->except('show');
    Route::post('services/ajax', [ServiceController::class, 'index'])->name('services.ajax');
    
    Route::get('services/get-item-row', [ServiceController::class, 'getItemRow'])->name('services.getItemRow');
    Route::get('services/get-item-details/{item_id}', [ServiceController::class, 'getItemDetails'])->name('services.getItemDetails');
    Route::patch('update-service-invoice', [ServiceController::class, 'updateInvoice'])->name('services.updateInvoice');
    Route::patch('update-service-estimate', [ServiceController::class, 'updateEstimate'])->name('services.updateEstimate');

    Route::resource('tags', TagController::class);
    Route::post('tags/ajax', [TagController::class, 'index'])->name('tags.ajax');

    Route::resource('industries', IndustryController::class);
    Route::post('industries/ajax', [IndustryController::class, 'index'])->name('industries.ajax');
    
    Route::resource('countries', CountryController::class);
    Route::post('countries/ajax', [CountryController::class, 'index'])->name('countries.ajax');
    
    Route::resource('currencies', CurrencyController::class);
    Route::post('currencies/ajax', [CurrencyController::class, 'index'])->name('currencies.ajax');
    
    Route::resource('timezones', TimezoneController::class);
    Route::post('timezones/ajax', [TimezoneController::class, 'index'])->name('timezones.ajax');

    # ACL
    Route::resource('role-groups', RoleGroupController::class);
    Route::post('role-groups/ajax', [RoleGroupController::class, 'index'])->name('role-groups.ajax');

    Route::resource('roles', RoleController::class);
    Route::post('roles/ajax', [RoleController::class, 'index'])->name('roles.ajax');
    Route::get('roles/permissions/{role}', [RoleController::class, 'getRolePermissions'])->name('roles.getPermissions');
    Route::put('roles/permissions/{role}', [RoleController::class, 'updateRolePermission'])->name('roles.permissions');

    Route::resource('permissions', PermissionController::class);
    Route::post('permissions/ajax', [PermissionController::class, 'index'])->name('permissions.ajax');

    Route::get('settings/{page?}', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
