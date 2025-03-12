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
    'prefix' => Raptor::getPath(),
    // 'as' => 'raptor.',
    // 'namespace' => Raptor::getNamespace('Http\Controllers'),
], function () { 
    Route::resource(__('tenants'), \Callcocam\Raptor\Http\Controllers\TenantController::class); 
    Route::resource(__('users'), \Callcocam\Raptor\Http\Controllers\UserController::class);
    Route::resource(__('roles'), \Callcocam\Raptor\Http\Controllers\RoleController::class);
    Route::resource(__('permissions'), \Callcocam\Raptor\Http\Controllers\PermissionController::class);
    Route::resource(__('abouts'), \Callcocam\Raptor\Http\Controllers\AboutController::class);
});