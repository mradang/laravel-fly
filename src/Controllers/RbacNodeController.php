<?php

namespace mradang\LaravelFly\Controllers;

use Illuminate\Http\Request;
use mradang\LaravelFly\Services\RbacNodeService;

class RbacNodeController extends Controller
{
    public function all()
    {
        return RbacNodeService::all();
    }

    public function allWithRole()
    {
        return RbacNodeService::allWithRole();
    }

    public function refresh()
    {
        return RbacNodeService::refresh();
    }

    public function syncRoles(Request $request)
    {
        $validatedData = $request->validate([
            'node_id' => 'required|integer|min:1',
            'roles' => 'nullable|array',
            'roles.*' => 'required|integer',
        ]);
        return RbacNodeService::syncRoles($validatedData['node_id'], $validatedData['roles']);
    }
}
