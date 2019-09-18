<?php

Route::group([
    'prefix' => config('fly.uri') . '/rbac',
    'namespace' => 'mradang\LaravelFly\Controllers',
    'middleware' => ['auth'],
], function () {
    Route::post('allNodes', 'RbacNodeController@all');
    Route::post('allNodesWithRole', 'RbacNodeController@allWithRole');
    Route::post('refreshNodes', 'RbacNodeController@refresh');

    Route::post('allRoles', 'RbacRoleController@all');
    Route::post('createRole', 'RbacRoleController@create');
    Route::post('findRoleWithNodes', 'RbacRoleController@findWithNodes');
    Route::post('syncRoleNodes', 'RbacRoleController@syncNodes');
    Route::post('updateRole', 'RbacRoleController@update');
    Route::post('deleteRole', 'RbacRoleController@delete');
    Route::post('saveRoleSort', 'RbacRoleController@saveSort');
});

Route::group([
    'prefix' => config('fly.uri') . '/log',
    'namespace' => 'mradang\LaravelFly\Controllers',
    'middleware' => ['auth'],
], function () {
    Route::post('lists', 'LogController@lists');
});
