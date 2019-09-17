<?php

namespace mradang\LaravelFly\Controllers;

use Illuminate\Http\Request;
use mradang\LaravelFly\Services\RbacNodeService;

class RbacNodeController extends Controller {

    public function all() {
        return RbacNodeService::all();
    }

    public function allWithRole() {
        return RbacNodeService::allWithRole();
    }

    public function refresh() {
        return RbacNodeService::refresh();
    }

}
