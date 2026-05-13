<?php

require_once __DIR__ . '/src/once.php';
require_once __DIR__ . '/src/mocks.php';

$csvFile = fopen(__DIR__ . '/results/break-even.csv', 'w');
fputcsv($csvFile, ['scenario', 'calls', 'without_once_ns', 'with_once_ns', 'diff_percent'], ',', '"', '');

// Simple operation
$simpleClosure = function () {
    $ip = Request::ip();
    $ua = Request::header('User-Agent');
    return $ip . '|' . $ua;
};

// HMAC operation
$hmacClosure = function () {
    $ip = Request::ip();
    $userAgent = Request::header('User-Agent');
    $dailySalt = now()->format('Y-m-d') . config('app.key');
    return hash_hmac('sha256', "{$ip}|{$userAgent}", $dailySalt);
};

// Heavy operation
$heavyClosure = function () {
    $ip = Request::ip();
    $ua = Request::header('User-Agent');
    $salt = now()->format('Y-m-d') . config('app.key');
    $result = '';
    for ($i = 0; $i < 10; $i++) {
        $result = hash_hmac('sha256', "{$ip}|{$ua}|{$i}", $salt);
    }
    $content = file_get_contents(__DIR__ . '/src/fixtures/small-file.txt');
    return $result . $content;
};

$scenarios = [
    'simple' => ['closure' => $simpleClosure, 'without' => function($n) {
        $start = hrtime(true);
        for ($i = 0; $i < $n; $i++) {
            $ip = Request::ip();
            $ua = Request::header('User-Agent');
            $result = $ip . '|' . $ua;
        }
        return hrtime(true) - $start;
    }],
    'hmac' => ['closure' => $hmacClosure, 'without' => function($n) {
        $start = hrtime(true);
        for ($i = 0; $i < $n; $i++) {
            $ip = Request::ip();
            $userAgent = Request::header('User-Agent');
            $dailySalt = now()->format('Y-m-d') . config('app.key');
            $result = hash_hmac('sha256', "{$ip}|{$userAgent}", $dailySalt);
        }
        return hrtime(true) - $start;
    }],
    'heavy' => ['closure' => $heavyClosure, 'without' => function($n) {
        $start = hrtime(true);
        for ($i = 0; $i < $n; $i++) {
            $ip = Request::ip();
            $ua = Request::header('User-Agent');
            $salt = now()->format('Y-m-d') . config('app.key');
            $result = '';
            for ($j = 0; $j < 10; $j++) {
                $result = hash_hmac('sha256', "{$ip}|{$ua}|{$j}", $salt);
            }
            $content = file_get_contents(__DIR__ . '/src/fixtures/small-file.txt');
            $result = $result . $content;
        }
        return hrtime(true) - $start;
    }]
];

$breakEvens = [];

foreach ($scenarios as $name => $data) {
    $breakEven = null;
    for ($n = 1; $n <= 50; $n++) {
        // Without once - run multiple times and average
        $withoutTimes = [];
        for ($r = 0; $r < 10; $r++) {
            $withoutTimes[] = $data['without']($n);
        }
        $withoutAvg = array_sum($withoutTimes) / count($withoutTimes);
        
        // With once - run multiple times and average (clear cache each time)
        $withTimes = [];
        for ($r = 0; $r < 10; $r++) {
            once_clear();
            $start = hrtime(true);
            for ($i = 0; $i < $n; $i++) {
                once($data['closure']);
            }
            $withTimes[] = hrtime(true) - $start;
        }
        $withAvg = array_sum($withTimes) / count($withTimes);
        
        $diff = $withoutAvg > 0 ? (($withoutAvg - $withAvg) / $withoutAvg) * 100 : 0;
        
        fputcsv($csvFile, [$name, $n, $withoutAvg, $withAvg, $diff], ',', '"', '');
        
        if ($breakEven === null && $withAvg < $withoutAvg) {
            $breakEven = $n;
        }
    }
    $breakEvens[$name] = $breakEven ?? 'never';
}

fclose($csvFile);

echo "Results written to results/break-even.csv\n\n";
echo "Break-even points:\n";
foreach ($breakEvens as $name => $point) {
    echo "$name: $point\n";
}
