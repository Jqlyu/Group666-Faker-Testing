<?php
declare(strict_types=1);

/**
 * Native CLI benchmarks for original Faker 1.9.2.
 * No PHPUnit loaded in this process. One metric per isolated process invocation.
 * Usage: php -d memory_limit=256M benchmarks/lyu-benchmark.php N01|N02
 */
if (PHP_OS_FAMILY !== 'Windows' || PHP_MAJOR_VERSION !== 7 || PHP_MINOR_VERSION !== 4) {
    fwrite(STDERR, "Benchmarks require native Windows and PHP 7.4; got " . PHP_OS_FAMILY . ' PHP ' . PHP_VERSION . "\n");
    exit(2);
}
if (ini_get('memory_limit') !== '256M') {
    fwrite(STDERR, "Expected PHP memory_limit=256M\n");
    exit(2);
}
$id = isset($argv[1]) ? $argv[1] : '';
if (!in_array($id, ['N01', 'N02'], true)) {
    fwrite(STDERR, "Specify N01 or N02\n");
    exit(2);
}
$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
date_default_timezone_set('UTC');
$faker = Faker\Factory::create('en_US');
$faker->seed(4160);

function generateRecord($faker): void
{
    // Same calls, order, random seed and workload for both benchmarks.
    // Discard outputs: memory metric excludes intentional retention of generated datasets.
    $faker->name();
    $faker->safeEmail();
    $faker->uuid();
}

$warmup = 100;
$batchSize = 1000;
$batchCount = 3;
for ($i = 0; $i < $warmup; ++$i) {
    generateRecord($faker);
}
$baseline = memory_get_usage(true);
$result = [
    'test_id' => $id,
    'tester' => 'Jiaqing Lyu',
    'os_family' => PHP_OS_FAMILY,
    'os_version' => php_uname('r'),
    'architecture' => php_uname('m'),
    'php_version' => PHP_VERSION,
    'faker_version' => '1.9.2',
    'faker_commit' => '848d8125239d7dbf8ab25cb7f054f1a630e68c2e',
    'php_memory_limit' => ini_get('memory_limit'),
    'execution_context' => 'native subprocess, without PHPUnit',
    'executed_at_utc' => gmdate('c'),
    'seed' => 4160,
    'locale' => 'en_US',
    'warmup_records' => $warmup,
    'batch_count' => $batchCount,
    'records_per_batch' => $batchSize,
    'measured_records' => $batchCount * $batchSize,
    'fields_per_record' => ['name', 'safeEmail', 'uuid'],
    'output_validation_in_timed_section' => false,
    'retains_generated_records' => false,
];
if ($id === 'N01') {
    $times = [];
    for ($batch = 0; $batch < $batchCount; ++$batch) {
        $start = hrtime(true);
        for ($i = 0; $i < $batchSize; ++$i) {
            generateRecord($faker);
        }
        $seconds = (hrtime(true) - $start) / 1000000000;
        $times[] = $seconds;
    }
    $sorted = $times;
    sort($sorted, SORT_NUMERIC);
    $median = $sorted[1];
    $result['batch_seconds'] = array_map(static function ($n) { return round($n, 9); }, $times);
    $result['median_seconds'] = round($median, 9);
    $result['throughput_records_per_second'] = round($batchSize / $median, 2);
    $result['throughput_note'] = 'Records per second calculated from 1000 divided by median batch seconds; measurement only, no acceptance threshold';
} else {
    for ($batch = 0; $batch < $batchCount; ++$batch) {
        for ($i = 0; $i < $batchSize; ++$i) {
            generateRecord($faker);
        }
    }
    $result['baseline_allocated_bytes'] = $baseline;
    $result['peak_allocated_bytes'] = memory_get_peak_usage(true);
    $result['peak_allocated_mib'] = round($result['peak_allocated_bytes'] / 1048576, 3);
    $result['peak_minus_baseline_bytes'] = max(0, $result['peak_allocated_bytes'] - $baseline);
    $result['metric_note'] = 'PHP allocator process peak via memory_get_peak_usage(true); includes PHP/Faker bootstrap, excludes PHPUnit; not total OS memory; no acceptance threshold';
}
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
