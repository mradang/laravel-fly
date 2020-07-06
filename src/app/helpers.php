<?php

use Illuminate\Support\Facades\Log;

if (! function_exists('L')) {

    function L($msg, $username = null) {
        \mradang\LaravelFly\Services\LogService::create($msg, $username);
    }

    function option(...$args) {
        if (count($args) === 1) {
            return \mradang\LaravelFly\Services\OptionService::get($args[0]);
        } else {
            return \mradang\LaravelFly\Services\OptionService::set($args[0], $args[1]);
        }
    }

    function debug() {
        $trace = debug_backtrace()[0];

        $stack = '';
        if (array_key_exists('file', $trace)) {
            $stack .= ',file:' . $trace['file'];
        }
        if (array_key_exists('line', $trace)) {
            $stack .= ',line:' . $trace['line'];
        }
        $stack = ltrim($stack, ',');

        Log::debug($stack."\n", func_get_args());
    }

    function change_log($model) {
        $old_data = $model->getOriginal();
        $change_data = $model->getDirty();
        $change_log = '';
        foreach ($change_data as $key => $value) {
            if (array_key_exists($key, $old_data) && $old_data[$key] != $value) {
                $change_log .= empty($change_log) ? '' : '，';
                $change_log .= sprintf("%s由「%s」改为「%s」", $key, $old_data[$key], $value);
            }
        }
        return $change_log;
    }

}
