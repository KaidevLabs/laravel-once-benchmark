<?php

require_once __DIR__ . '/../once.php';
require_once __DIR__ . '/../mocks.php';

use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\Subject;

class IterationBreakEvenBench
{
    private $simpleClosure;
    private $hmacClosure;
    private $heavyClosure;

    public function __construct()
    {
        $this->simpleClosure = function () {
            $ip = Request::ip();
            $ua = Request::header('User-Agent');
            return $ip . '|' . $ua;
        };

        $this->hmacClosure = function () {
            $ip = Request::ip();
            $userAgent = Request::header('User-Agent');
            $dailySalt = now()->format('Y-m-d') . config('app.key');
            return hash_hmac('sha256', "{$ip}|{$userAgent}", $dailySalt);
        };

        $this->heavyClosure = function () {
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

    public function provideCallCounts(): array
    {
        $counts = [];
        for ($i = 1; $i <= 50; $i++) {
            $counts[] = ['calls' => $i];
        }
        return $counts;
    }

    #[Subject]
    #[ParamProviders('provideCallCounts')]
    public function benchSimpleWithoutOnce($params)
    {
        $calls = $params['calls'];
        for ($i = 0; $i < $calls; $i++) {
            $ip = Request::ip();
            $ua = Request::header('User-Agent');
            $result = $ip . '|' . $ua;
        }
    }

    #[Subject]
    #[ParamProviders('provideCallCounts')]
    public function benchSimpleWithOnce($params)
    {
        $calls = $params['calls'];
        for ($i = 0; $i < $calls; $i++) {
            once($this->simpleClosure);
        }
    }

    #[Subject]
    #[ParamProviders('provideCallCounts')]
    public function benchHmacWithoutOnce($params)
    {
        $calls = $params['calls'];
        for ($i = 0; $i < $calls; $i++) {
            $ip = Request::ip();
            $userAgent = Request::header('User-Agent');
            $dailySalt = now()->format('Y-m-d') . config('app.key');
            $result = hash_hmac('sha256', "{$ip}|{$userAgent}", $dailySalt);
        }
    }

    #[Subject]
    #[ParamProviders('provideCallCounts')]
    public function benchHmacWithOnce($params)
    {
        $calls = $params['calls'];
        for ($i = 0; $i < $calls; $i++) {
            once($this->hmacClosure);
        }
    }

    #[Subject]
    #[ParamProviders('provideCallCounts')]
    public function benchHeavyWithoutOnce($params)
    {
        $calls = $params['calls'];
        for ($i = 0; $i < $calls; $i++) {
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
    #[ParamProviders('provideCallCounts')]
    public function benchHeavyWithOnce($params)
    {
        $calls = $params['calls'];
        for ($i = 0; $i < $calls; $i++) {
            once($this->heavyClosure);
        }
    }
}
