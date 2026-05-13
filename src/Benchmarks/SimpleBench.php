<?php

require_once __DIR__ . '/../once.php';
require_once __DIR__ . '/../mocks.php';

use PhpBench\Attributes\Subject;

class SimpleBench
{
    private $closure;

    public function __construct()
    {
        $this->closure = function () {
            $ip = Request::ip();
            $ua = Request::header('User-Agent');
            return $ip . '|' . $ua;
        };
    }

    #[Subject]
    public function benchWithoutOnce()
    {
        for ($i = 0; $i < 100; $i++) {
            $ip = Request::ip();
            $ua = Request::header('User-Agent');
            $result = $ip . '|' . $ua;
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
