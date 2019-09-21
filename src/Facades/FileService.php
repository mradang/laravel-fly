<?php

namespace mradang\LaravelFly\Facades;

use mradang\LaravelFly\Services\FileService as Accessor;

class FileService extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Accessor::class;
    }
}
