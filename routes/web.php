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
use Callcocam\Raptor\Http\Controllers\NotificationController;

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
    // Rotas bulk actions
    Route::post('/tenants/bulk-action', [TenantController::class, 'bulkAction'])->name('tenants.bulk-action');


    // Rotas para gerenciamento de Usuários
    Route::resource('users', UserController::class);
    // Rotas bulk actions
    Route::post('/users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');



    // Rotas para gerenciamento de Papéis
    Route::resource('roles', RoleController::class);
    // Rotas bulk actions
    Route::post('/roles/bulk-action', [RoleController::class, 'bulkAction'])->name('roles.bulk-action');


    // Rotas para gerenciamento de Permissões
    Route::resource('permissions', PermissionController::class);
    // Rotas bulk actions
    Route::post('/permissions/bulk-action', [PermissionController::class, 'bulkAction'])->name('permissions.bulk-action');

    // 🔔 Rotas para sistema de notificações
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/', [NotificationController::class, 'destroyAll'])->name('destroy-all');
    });
});
