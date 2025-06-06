<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\Raptor\Routes;

use Illuminate\Support\Facades\Route;
use Callcocam\Raptor\Http\Controllers\UserController;
use Callcocam\Raptor\Http\Controllers\TenantController;
use Callcocam\Raptor\Http\Controllers\RoleController;
use Callcocam\Raptor\Http\Controllers\PermissionController;
use Callcocam\Raptor\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes - Raptor Package
|--------------------------------------------------------------------------
|
| Rotas para o painel administrativo do pacote Raptor.
| Todas as rotas estão protegidas por autenticação e agrupadas
| sob o prefix 'admin'.
|
*/

Route::middleware(['auth', 'verified', 'web'])->prefix('admin')->name('admin.')->group(function () {

    // Rotas para o Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Rotas para gerenciamento de Tenants
    Route::resource('tenants', TenantController::class);
    
    // Rotas para gerenciamento de Usuários
    Route::resource('users', UserController::class);
    
    // Rotas para gerenciamento de Papéis
    Route::resource('roles', RoleController::class);
    
    // Rotas para gerenciamento de Permissões
    Route::resource('permissions', PermissionController::class);
    
});
