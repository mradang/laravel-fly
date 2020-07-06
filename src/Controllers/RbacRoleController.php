<?php

namespace mradang\LaravelFly\Controllers;

use Illuminate\Http\Request;
use mradang\LaravelFly\Services\RbacRoleService;

class RbacRoleController extends Controller
{
    private $messages = [
        'name.unique' => '角色名已经存在！',
    ];

    public function all()
    {
        return RbacRoleService::all();
    }

    public function allWithUsers()
    {
        return RbacRoleService::allWithUsers();
    }

    public function findWithNodes(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|min:1',
        ]);
        return RbacRoleService::findWithNodes($request->input('id'));
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|min:1',
        ]);
        return RbacRoleService::delete($request->input('id'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:rbac_role',
        ], $this->messages);
        return RbacRoleService::create($request->only('name'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'name' => 'required|unique:rbac_role,name,' . $request->input('id'),
        ], $this->messages);
        return RbacRoleService::update($request->only('id', 'name'));
    }

    public function syncNodes(Request $request)
    {
        $validatedData = $request->validate([
            'role_id' => 'required|integer|min:1',
            'nodes' => 'nullable|array',
            'nodes.*' => 'required|integer',
        ]);
        RbacRoleService::syncNodes($validatedData['role_id'], $validatedData['nodes']);
    }

    public function saveSort(Request $request)
    {
        $validatedData = $request->validate([
            '*.id' => 'required|integer',
            '*.sort' => 'required|integer',
        ]);
        RbacRoleService::saveSort($validatedData);
    }
}
