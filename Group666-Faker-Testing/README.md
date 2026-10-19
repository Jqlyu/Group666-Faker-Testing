# Group 666 - Faker Software Testing

This repository contains only the four members' test source code and shared test configuration.
There are **22 distinct functional cases** (F01-F22) and **2 non-functional cases** (N01-N02).
Execution screenshots and final results belong in the group's Word report, not this repository.

## Members and test ownership

| Member | Operating system | Cases | Source |
| --- | --- | --- | --- |
| Zhiyan Chang | macOS | F01-F06 | `tests/member-macos/` |
| Haochang Shi | Linux | F07-F12 | `tests/member-linux/` |
| Jiaqing Lyu | Native Windows | F13-F16, N01-N02 | `tests/member-windows-lyu/`, `benchmarks/` |
| Jingyi Zhu | Windows | F17-F22 | `tests/member-windows-zhu/` |

## Dependencies

- Original `fzaninotto/faker` v1.9.2 (not the maintained `fakerphp/faker` fork)
- PHP 7.4.33 (project baseline)
- Composer 2.x
- PHPUnit 9.6.38 for F01-F16 and N01-N02
- F17-F22 use a standalone PHP test runner, loaded through the Composer autoloader

Run `composer install` on a PHP 7.4 environment. Composer downloads the exact Faker package,
so do not upload a separate Faker source archive or the `vendor/` directory.
The repository does not include a `composer.lock`; generate it after dependency installation
and keep it consistent when members agree to freeze dependencies.

## Commands

```bash
# macOS: F01-F06
vendor/bin/phpunit --configuration phpunit.xml --testsuite MacOS --testdox

# Linux: F07-F12
vendor/bin/phpunit --configuration phpunit.xml --testsuite Linux --testdox
```

Native Windows PowerShell, after `composer install`:

```powershell
# F13-F16 and N01-N02
powershell -ExecutionPolicy Bypass -File .\run-windows-lyu.ps1

# F17-F22
powershell -ExecutionPolicy Bypass -File .\run-windows-zhu.ps1
```

N01 and N02 create separate PHP processes using `benchmarks/lyu-benchmark.php`.
They measure throughput and peak allocated PHP memory, respectively; these are
measurements, not comparisons to predefined performance acceptance thresholds.

## Evidence and reporting

- Place Windows execution screenshots, Linux screenshots and macOS screenshots in the final Word report.
- Preserve raw logs locally to substantiate the final report.
- Keep `evidence/`, `results/`, `vendor/`, and Composer downloaded dependencies out of Git.
- Do not claim a PASS unless the test was actually executed and the result was recorded.
- The course report is a separate deliverable. A GitHub link does not replace it.

## Integration notes

The source test logic was copied from the members' supplied ZIP files. Integration-only
changes are: a shared Composer autoloader and PHPUnit configuration, a root-relative path
for Lyu's benchmark subprocess, and a Composer autoload path for Zhu's standalone runner.
These changes do not constitute a fresh cross-platform execution or validation. Each member
should run their own suite again in their assigned operating system before final submission.

The original Faker version is archived and PHP 7.4 is past end of support. Use only for
isolated coursework and do not deploy publicly.
