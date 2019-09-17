<?php

namespace mradang\LaravelFly\Controllers;

use Illuminate\Http\Request;
use mradang\LaravelFly\Services\LogService;

class LogController extends Controller {

    public function lists(Request $request) {
        $this->validate($request, [
            'page' => 'required|integer|min:1',
            'pagesize' => 'required|integer|min:1',
            'username' => 'string',
            'log_msg' => 'string',
            'ip' => 'ip',
        ]);
        return LogService::lists(
            $request->only('username', 'log_msg', 'ip'),
            $request->page,
            $request->pagesize
        );
    }

}
