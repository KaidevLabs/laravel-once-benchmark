<?php

if (!function_exists('once')) {
    function once(\Closure $callback)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $key = spl_object_hash($callback) . ($trace[1]['file'] ?? '') . ($trace[1]['line'] ?? '');
        if (!isset($GLOBALS['once_cache'][$key])) {
            $GLOBALS['once_cache'][$key] = $callback();
        }
        return $GLOBALS['once_cache'][$key];
    }

    function once_clear()
    {
        $GLOBALS['once_cache'] = [];
    }
}
