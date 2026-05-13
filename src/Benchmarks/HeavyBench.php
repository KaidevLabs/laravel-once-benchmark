<?php

require_once __DIR__ . '/../once.php';
require_once __DIR__ . '/../mocks.php';

use PhpBench\Attributes\Subject;

class HeavyBench
{
    private $closure;

    public function __construct()
    {
        $this->closure = function () {
            $ip = Request::ip();
            $ua = Request::header('User-Agent');
            $salt = now()->format('Y-m-d') . config('app.key');
            $result = '';
            for ($i = 0; $i < 10; $i++) {
                $result = hash_hmac('sha256', "{$ip}|{$ua}|{$i}", $salt);
            }
            $content = file_get_contents(__DIR__ . '/../fixtures/small-file.txt');
            return $result . $content;
        };
    }

    #[Subject]
    public function benchWithoutOnce()
    {
        for ($i = 0; $i < 100; $i++) {
            $ip = Request::ip();
            $ua = Request::header('User-Agent');
            $salt = now()->format('Y-m-d') . config('app.key');
            $result = '';
            for ($j = 0; $j < 10; $j++) {
                $result = hash_hmac('sha256', "{$ip}|{$ua}|{$j}", $salt);
            }
            $content = file_get_contents(__DIR__ . '/../fixtures/small-file.txt');
            $result = $result . $content;
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
