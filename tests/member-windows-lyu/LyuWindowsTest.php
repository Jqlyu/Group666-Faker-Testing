<?php
declare(strict_types=1);

namespace Group666\MemberWindows;

use Faker\Factory;
use PHPUnit\Framework\TestCase;

/**
 * Jiaqing Lyu: Windows-native Faker v1.9.2 system-level API checks.
 * F13-F16: correctness. N01-N02: isolated subprocess benchmarks.
 * PHP 7.4.33 / PHPUnit 9.6.x; one public test method per assigned ID.
 */
final class LyuWindowsTest extends TestCase
{
    private const SEED = 4160;

    private function faker()
    {
        $faker = Factory::create('en_US');
        $faker->seed(self::SEED);
        return $faker;
    }

    public function testF13DateRangeUtc(): void
    {
        $faker = $this->faker();
        $from = new \DateTime('2020-01-01 00:00:00', new \DateTimeZone('UTC'));
        $to = new \DateTime('2020-12-31 23:59:59', new \DateTimeZone('UTC'));
        $samples = [];
        for ($i = 0; $i < 100; ++$i) {
            $date = $faker->dateTimeBetween($from, $to, 'UTC');
            $this->assertInstanceOf(\DateTime::class, $date, 'F13 sample ' . $i);
            $this->assertGreaterThanOrEqual($from->getTimestamp(), $date->getTimestamp());
            $this->assertLessThanOrEqual($to->getTimestamp(), $date->getTimestamp());
            $this->assertSame('UTC', $date->getTimezone()->getName());
            $samples[] = $date->format(DATE_ATOM);
        }
        $this->record('F13', [
            'count' => count($samples), 'seed' => self::SEED, 'locale' => 'en_US',
            'start_utc' => $from->format(DATE_ATOM), 'end_utc' => $to->format(DATE_ATOM),
            'first_samples' => array_slice($samples, 0, 5),
            'checks' => ['DateTime type', 'inclusive timestamps', 'UTC timezone'],
        ]);
    }

    public function testF14UuidV3AndRfcVariant(): void
    {
        $faker = $this->faker();
        $samples = [];
        for ($i = 0; $i < 100; ++$i) {
            $uuid = $faker->uuid();
            $this->assertIsString($uuid, 'F14 sample ' . $i);
            // Original Faker 1.9.2 generates version 3 UUIDs, not version 4.
            $this->assertMatchesRegularExpression(
                '/\\A[0-9a-f]{8}-[0-9a-f]{4}-3[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\\z/',
                $uuid,
                'F14 sample ' . $i
            );
            $samples[] = $uuid;
        }
        $this->assertCount(100, array_unique($samples));
        $this->record('F14', [
            'count' => count($samples), 'seed' => self::SEED,
            'unique_in_sample' => count(array_unique($samples)),
            'first_samples' => array_slice($samples, 0, 5),
            'checks' => ['lowercase 8-4-4-4-12', 'version 3', 'RFC 4122 variant'],
        ]);
    }

    public function testF15SafeEmailReservedDomains(): void
    {
        $faker = $this->faker();
        $allowed = ['example.com', 'example.net', 'example.org'];
        $samples = [];
        for ($i = 0; $i < 100; ++$i) {
            $email = $faker->safeEmail();
            $this->assertIsString($email, 'F15 sample ' . $i);
            $this->assertNotFalse(filter_var($email, FILTER_VALIDATE_EMAIL), 'F15: invalid email syntax');
            $this->assertSame(0, preg_match('/\\s/u', $email), 'F15: no spaces');
            $at = strrpos($email, '@');
            $this->assertNotFalse($at, 'F15: missing @');
            $domain = substr($email, $at + 1);
            $this->assertContains($domain, $allowed, 'F15: not a reserved example domain');
            $samples[] = $email;
        }
        $this->record('F15', [
            'count' => count($samples), 'seed' => self::SEED, 'locale' => 'en_US',
            'allowed_domains' => $allowed, 'first_samples' => array_slice($samples, 0, 5),
            'checks' => ['valid email syntax', 'no spaces', 'reserved domains'],
        ]);
    }

    public function testF16Ipv4Validation(): void
    {
        $faker = $this->faker();
        $samples = [];
        for ($i = 0; $i < 100; ++$i) {
            $ip = $faker->ipv4();
            $this->assertIsString($ip, 'F16 sample ' . $i);
            $this->assertNotFalse(
                filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4),
                'F16: invalid IPv4 address'
            );
            $samples[] = $ip;
        }
        $this->record('F16', [
            'count' => count($samples), 'seed' => self::SEED,
            'first_samples' => array_slice($samples, 0, 5),
            'checks' => ['valid IPv4 via filter_var(FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)'],
        ]);
    }

    public function testN01FixedWorkloadThroughputBaseline(): void
    {
        $data = $this->benchmark('N01');
        $this->assertSame('N01', $data['test_id']);
        $this->assertSame(100, $data['warmup_records']);
        $this->assertSame(1000, $data['records_per_batch']);
        $this->assertCount(3, $data['batch_seconds']);
        foreach ($data['batch_seconds'] as $seconds) {
            $this->assertGreaterThan(0.0, $seconds);
        }
        $this->assertGreaterThan(0.0, $data['median_seconds']);
        $this->assertGreaterThan(0.0, $data['throughput_records_per_second']);
        // An executed numerical baseline is not an SLA/performance-threshold PASS.
    }

    public function testN02IsolatedPeakMemoryBaseline(): void
    {
        $data = $this->benchmark('N02');
        $this->assertSame('N02', $data['test_id']);
        $this->assertSame('256M', $data['php_memory_limit']);
        $this->assertSame(100, $data['warmup_records']);
        $this->assertSame(3000, $data['measured_records']);
        $this->assertGreaterThan(0, $data['peak_allocated_bytes']);
        $this->assertGreaterThanOrEqual($data['baseline_allocated_bytes'], $data['peak_allocated_bytes']);
        // memory_get_peak_usage(true) reports PHP allocator peak, not OS-wide memory.
    }

    private function benchmark(string $id): array
    {
        $root = dirname(__DIR__, 2);
        $script = $root . '/benchmarks/lyu-benchmark.php';
        $command = [PHP_BINARY, '-d', 'memory_limit=256M', $script, $id];
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $process = proc_open($command, $descriptors, $pipes, $root);
        $this->assertTrue(is_resource($process), 'N benchmark subprocess could not start');
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);
        $this->assertSame(0, $exitCode, $id . ' failed: ' . $stderr . ' ' . $stdout);
        $data = json_decode($stdout, true);
        $this->assertIsArray($data, $id . ' must output valid JSON: ' . $stdout);
        $this->assertSame('Windows', $data['os_family'], 'N benchmark must run as native Windows process');
        $this->assertSame(PHP_VERSION, $data['php_version']);
        $this->record($id, $data);
        $path = $root . '/evidence/results/benchmarks';
        if (!is_dir($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
            throw new \RuntimeException('Cannot create benchmark evidence directory: ' . $path);
        }
        if (file_put_contents($path . '/' . $id . '.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n") === false) {
            throw new \RuntimeException('Cannot write benchmark evidence: ' . $id);
        }
        return $data;
    }

    private function record(string $id, array $data): void
    {
        $root = dirname(__DIR__, 2);
        $dir = getenv('FAKER_TEST_EVIDENCE_DIR');
        if (!$dir) {
            $dir = $root . '/evidence/results/observations';
        }
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException('Cannot create observation directory: ' . $dir);
        }
        $document = array_merge([
            'test_id' => $id, 'tester' => 'Jiaqing Lyu',
            'os_family' => PHP_OS_FAMILY, 'php_version' => PHP_VERSION,
            'recorded_at_utc' => gmdate('c'),
        ], $data);
        if (file_put_contents($dir . '/' . $id . '.json', json_encode($document, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n") === false) {
            throw new \RuntimeException('Cannot write observation: ' . $id);
        }
    }
}
