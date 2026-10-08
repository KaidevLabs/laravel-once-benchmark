<div align="center">

<a href="https://kaidev.io/">
  <img src="https://kaidev.io/images/isotype_gradient.svg" width="42" alt="Kaidev">
</a>

<h1 align="center">Evolve your software with purpose</h1>

<p>
  <a href="https://kaidev.io/#philosophy"><img src="https://img.shields.io/badge/Philosophy-Kaizen-ea580c?style=flat-square" alt="Philosophy: Kaizen"></a>
  <a href="https://kaidev.io/#about"><img src="https://img.shields.io/badge/Value-Technical%20excellence-16a34a?style=flat-square" alt="Value: Technical excellence"></a>
  <a href="https://kaidev.io/#contact"><img src="https://img.shields.io/badge/Focus-Let%27s%20talk-2563eb?style=flat-square" alt="Focus: Let's talk"></a>
</p>

<h3 align="center">
  Kaidev is a software consultancy specializing in development and code refactoring.
  Guided by Kaizen, we strengthen architecture, performance, and automation to turn
  technical challenges into a competitive advantage.
</h3>

<p>
  <a href="https://kaidev.io/">Website</a> ·
  <a href="https://www.linkedin.com/company/kaidev/">LinkedIn</a> ·
  <a href="https://bsky.app/profile/jpascual.kaidev.io">Bluesky</a>
</p>

</div>


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
