<?php

use Illuminate\Support\Facades\Log;

if (! function_exists('debug')) {

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

}
