<?php

use Illuminate\Support\Facades\Route;
use mradang\LaravelFly\Controllers\LogController;
use mradang\LaravelFly\Controllers\RbacNodeController;
use mradang\LaravelFly\Controllers\RbacRoleController;

Route::group([
    'prefix' => 'api/fly',
    'middleware' => ['auth'],
], function () {
    Route::group(['prefix' => 'rbac'], function () {
        Route::post('allNodes', [RbacNodeController::class, 'all']);
        Route::post('allNodesWithRole', [RbacNodeController::class, 'allWithRole']);
        Route::post('refreshNodes', [RbacNodeController::class, 'refresh']);

        Route::post('allRoles', [RbacRoleController::class, 'all']);
        Route::post('createRole', [RbacRoleController::class, 'create']);
        Route::post('findRoleWithNodes', [RbacRoleController::class, 'findWithNodes']);
        Route::post('syncNodeRoles', [RbacNodeController::class, 'syncRoles']);
        Route::post('syncRoleNodes', [RbacRoleController::class, 'syncNodes']);
        Route::post('updateRole', [RbacRoleController::class, 'update']);
        Route::post('deleteRole', [RbacRoleController::class, 'delete']);
        Route::post('saveRoleSort', [RbacRoleController::class, 'saveSort']);
    });

    Route::group(['prefix' => 'log'], function () {
        Route::post('lists', [LogController::class, 'lists']);
    });
});
