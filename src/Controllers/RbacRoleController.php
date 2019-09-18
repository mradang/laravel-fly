<?php

namespace mradang\LaravelFly\Controllers;

use Illuminate\Http\Request;
use mradang\LaravelFly\Services\RbacRoleService;

class RbacRoleController extends Controller {

    private $messages = [
        'name.unique' => '角色名已经存在！',
    ];

    public function all() {
        return RbacRoleService::all();
    }

    public function allWithUsers() {
        return RbacRoleService::allWithUsers();
    }

    public function findWithNodes(Request $request) {
        $this->validate($request, [
            'id' => 'required|integer|min:1',
        ]);
        return RbacRoleService::findWithNodes($request->input('id'));
    }

    public function delete(Request $request) {
        $this->validate($request, [
            'id' => 'required|integer|min:1',
        ]);
        return RbacRoleService::delete($request->input('id'));
    }

    public function create(Request $request) {
        $this->validate($request, [
            'name' => 'required|unique:rbac_role',
        ], $this->messages);
        return RbacRoleService::create($request->only('name'));
    }

    public function update(Request $request) {
        $this->validate($request, [
            'id' => 'required|integer',
            'name' => 'required|unique:rbac_role,name,'.$request->input('id'),
        ], $this->messages);
        return RbacRoleService::update($request->only('id', 'name'));
    }

    public function syncNodes(Request $request) {
        $this->validate($request, [
            'role_id' => 'required|integer|min:1',
            'nodes' => ['Regex:/^\d+(,\d+)*$/'],
        ]);
        $nodes = empty($request->input('nodes')) ? [] : explode(',', $request->input('nodes'));
        RbacRoleService::syncNodes($request->input('role_id'), $nodes);
    }

    public function saveSort(Request $request) {
        $validatedData = $this->validate($request, [
            '*.id' => 'required|integer',
            '*.sort' => 'required|integer',
        ]);
        RbacRoleService::saveSort($validatedData['*']);
    }

}
