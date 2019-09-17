<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'rbac',
    'middleware' => ['auth'],
], function () {
    Route::get('allNodes', 'RbacNodeController@all');
    Route::get('allNodesWithRole', 'RbacNodeController@allWithRole');
    Route::get('refreshNodes', 'RbacNodeController@refresh');

    Route::get('allRoles', 'RbacRoleController@all');
    Route::post('createRole', 'RbacRoleController@create');
    Route::post('findRoleWithNodes', 'RbacRoleController@findWithNodes');
    Route::post('syncRoleNodes', 'RbacRoleController@syncNodes');
    Route::post('updateRole', 'RbacRoleController@update');
    Route::post('deleteRole', 'RbacRoleController@delete');
    Route::post('saveRoleSort', 'RbacRoleController@saveSort');
});

Route::group([
    'prefix' => 'log',
    'middleware' => ['auth'],
], function () {
    Route::post('lists', 'LogController@lists');
});
