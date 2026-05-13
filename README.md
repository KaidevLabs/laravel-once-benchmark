# once() Benchmark

Benchmark comparing `once()` vs no `once()` for PHP operations of varying cost. Finds break-even point where caching becomes beneficial.

## Purpose

Show when `once()` worth using: expensive ops where cache overhead offset by saved work.

## Setup

1. `composer install`
2. `php -d memory_limit=512M run-break-even.php`

## Results

### Fixed Iterations (100 calls each)

| Scenario | Without once() (mean) | With once() (mean) | Diff |
|----------|------------------------|---------------------|------|
| Simple (string concat) | 10.151μs | 56.789μs | -459% (slower) |
| HMAC (user code) | 151.413μs | 70.773μs | +53% (faster) |
| Heavy (10x HMAC + file read) | 2174μs | 97.166μs | +2137% (faster) |

### Break-Even Analysis (1-50 calls)

| Scenario | Op Cost (ns) | once() Overhead (ns) | Break-Even Point |
|----------|----------------|----------------------|-------------------|
| Simple | ~430 | ~933 | Never |
| HMAC | ~15239 | ~2875 | 1 call |
| Heavy | ~15000+ | ~varies | 1 call |

## Analysis

- **Simple**: `once()` never beneficial. Overhead (933ns) > op cost (430ns).
- **HMAC**: `once()` faster even at 1 call. Op cost >> overhead.
- **Heavy**: `once()` dramatically faster. Caching very beneficial.

## Key Takeaway

`once()` beneficial for ops > ~1μs. Cheap ops: overhead dominates. Expensive ops: caching wins.

## Graph Guide

1. Run `php run-break-even.php`
2. Import `results/break-even.csv` to Google Sheets/Excel
3. Chart: x-axis = calls, y-axis = time (ns), overlay with/without once()
4. Break-even = where "with once()" line crosses below "without once()"
