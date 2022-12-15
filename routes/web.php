<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\IndustryController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\TimezoneController;
use App\Http\Controllers\RoleGroupController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;

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
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    
    Route::get('change-password', [UserController::class, 'changePassword'])->name('change-password');
    Route::post('change-password', [UserController::class, 'updatePassword'])->name('users.change-password');
    
    Route::resource('users', UserController::class);
    Route::post('users/ajax', [UserController::class, 'index'])->name('users.ajax');
    
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

    Route::resource('permissions', PermissionController::class);
    Route::post('permissions/ajax', [PermissionController::class, 'index'])->name('permissions.ajax');
});

require __DIR__.'/auth.php';
