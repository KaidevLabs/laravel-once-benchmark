<?php

require_once __DIR__ . '/../once.php';
require_once __DIR__ . '/../mocks.php';

use PhpBench\Attributes\Subject;

class HmacBench
{
    private $closure;

    public function __construct()
    {
        $this->closure = function () {
            $ip = Request::ip();
            $userAgent = Request::header('User-Agent');
            $dailySalt = now()->format('Y-m-d') . config('app.key');
            return hash_hmac('sha256', "{$ip}|{$userAgent}", $dailySalt);
        };
    }

    #[Subject]
    public function benchWithoutOnce()
    {
        for ($i = 0; $i < 100; $i++) {
            $ip = Request::ip();
            $userAgent = Request::header('User-Agent');
            $dailySalt = now()->format('Y-m-d') . config('app.key');
            $result = hash_hmac('sha256', "{$ip}|{$userAgent}", $dailySalt);
        }
    }

    #[Subject]
    public function benchWithOnce()
    {
        for ($i = 0; $i < 100; $i++) {
            once($this->closure);
        }
    }
}
