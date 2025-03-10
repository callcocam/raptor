<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
use Callcocam\Raptor\Facades\Raptor;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['web', 'auth'],
    'prefix' => Raptor::path(),
    'as' => 'raptor.',
    'namespace' => Raptor::getNamespace('Http\Controllers'),
], function () {
    Route::resource(__('raptor::raptor.tenants'), \Callcocam\Raptor\Http\Controllers\TenantController::class); 
    Route::resource(__('raptor::raptor.users'), \Callcocam\Raptor\Http\Controllers\UserController::class);
    Route::resource(__('raptor::raptor.roles'), \Callcocam\Raptor\Http\Controllers\RoleController::class);
    Route::resource(__('raptor::raptor.permissions'), \Callcocam\Raptor\Http\Controllers\PermissionController::class);
    Route::resource(__('raptor::raptor.abouts'), \Callcocam\Raptor\Http\Controllers\AboutController::class);
});