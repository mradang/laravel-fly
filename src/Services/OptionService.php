<?php

namespace mradang\LaravelFly\Services;

use mradang\LaravelFly\Models\Option;

class OptionService
{
    public static function set($key, $value)
    {
        $option = Option::firstOrNew(['option_name' => $key]);
        $option->option_value = $value;
        return $option->save();
    }

    public static function get($option_name)
    {
        return Option::where('option_name', $option_name)->value('option_value');
    }
}
