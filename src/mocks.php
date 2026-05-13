<?php

class Request
{
    public static function ip()
    {
        return '127.0.0.1';
    }

    public static function header(string $key)
    {
        if ($key === 'User-Agent') {
            return 'BenchmarkBot/1.0';
        }
        return null;
    }
}

if (!function_exists('config')) {
    function config(string $key)
    {
        if ($key === 'app.key') {
            return 'benchmark-app-key-1234';
        }
        return null;
    }
}

if (!function_exists('now')) {
    function now()
    {
        return new class {
            public function format(string $format)
            {
                return '2026-05-13';
            }
        };
    }
}
