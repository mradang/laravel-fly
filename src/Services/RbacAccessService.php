<?php

namespace mradang\LaravelFly\Services;

use mradang\LaravelFly\Models\RbacAccess;

class RbacAccessService
{
    public static function clearInvalidAccess()
    {
        $ids = RbacNodeService::ids();
        RbacAccess::whereNotIn('node_id', $ids)->delete();
    }
}
