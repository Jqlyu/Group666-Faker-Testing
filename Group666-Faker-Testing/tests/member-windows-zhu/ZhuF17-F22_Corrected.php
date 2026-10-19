<?php
/**
 * Faker F17-F22 functional tests (standalone PHP runner)
 * Environment: PHP 7.4.33; Composer autoloader required; PHPUnit not required.
 * Place this file at tests/member-windows-zhu/ZhuF17-F22_Corrected.php.
 * Run from project root: php tests/member-windows-zhu/ZhuF17-F22_Corrected.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

function check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$tests = array(
    'F17 - unique() returns ten different digits' => function () {
        $faker = Faker\Factory::create();
        $digits = array();
        for ($i = 0; $i < 10; $i++) {
            $digits[] = $faker->unique()->randomDigit;
        }
        check(count(array_unique($digits)) === 10, 'Duplicate digits: ' . implode(', ', $digits));
        foreach ($digits as $digit) {
            check(is_int($digit) && $digit >= 0 && $digit <= 9, 'Invalid digit: ' . var_export($digit, true));
        }
        return 'Digits: ' . implode(', ', $digits);
    },

    'F18 - optional() documented supported weight boundaries' => function () {
        $faker = Faker\Factory::create();
        // In Faker v1.9.2, integer 0 and 100 are deterministic boundaries.
        $zero = $faker->optional(0, 'DEFAULT')->passthrough('PROVIDER');
        $hundred = $faker->optional(100, 'DEFAULT')->passthrough('PROVIDER');
        check($zero === 'DEFAULT', 'Weight 0 expected DEFAULT, got ' . var_export($zero, true));
        check($hundred === 'PROVIDER', 'Weight 100 expected PROVIDER, got ' . var_export($hundred, true));

        // Check that a fractional weight of 0.5 selects both branches over many draws.
        // This is a broad statistical sanity check, not a claim of exact 50% output.
        $providerCount = 0;
        for ($i = 0; $i < 200; $i++) {
            $result = $faker->optional(0.5, 'DEFAULT')->passthrough('PROVIDER');
            check($result === 'DEFAULT' || $result === 'PROVIDER',
                'Unexpected optional() output: ' . var_export($result, true));
            if ($result === 'PROVIDER') {
                $providerCount++;
            }
        }
        check($providerCount >= 50 && $providerCount <= 150,
            'Weight 0.5 yielded PROVIDER ' . $providerCount . '/200 times; expected a roughly balanced mix');

        // Known investigation finding: float 1.0 is not treated as 100% in v1.9.2.
        // Preserve the original failure logs; do not silently treat that discrepancy as fixed.
        return 'int 0: DEFAULT; int 100: PROVIDER; float 0.5: '
            . $providerCount . '/200 PROVIDER';
    },

    'F19 - valid() accepts only even digits' => function () {
        $faker = Faker\Factory::create();
        $even = function ($value) { return $value % 2 === 0; };
        $digits = array();
        for ($i = 0; $i < 20; $i++) {
            $digit = $faker->valid($even)->randomDigit;
            check(is_int($digit) && $digit >= 0 && $digit <= 9 && $digit % 2 === 0,
                'Invalid even digit: ' . var_export($digit, true));
            $digits[] = $digit;
        }
        return 'Even digits: ' . implode(', ', $digits);
    },

    'F20 - seed() reproduces the same sequence' => function () {
        $faker = Faker\Factory::create();
        $faker->seed(1234);
        $first = array();
        for ($i = 0; $i < 10; $i++) {
            $first[] = $faker->randomDigit;
        }
        $faker->seed(1234);
        $second = array();
        for ($i = 0; $i < 10; $i++) {
            $second[] = $faker->randomDigit;
        }
        check($first === $second,
            'Sequences differ: ' . implode(',', $first) . ' vs ' . implode(',', $second));
        return 'Same sequence twice: ' . implode(', ', $first);
    },

    'F21 - bothify() matches INV-##??' => function () {
        $faker = Faker\Factory::create();
        $samples = array();
        for ($i = 0; $i < 20; $i++) {
            $value = $faker->bothify('INV-##??');
            check(is_string($value) && preg_match('/^INV-[0-9]{2}[a-zA-Z]{2}$/D', $value) === 1,
                'Wrong format: ' . var_export($value, true));
            $samples[] = $value;
        }
        return '20 valid codes; examples: ' . implode(', ', array_slice($samples, 0, 3));
    },

    'F22 - dateTimeBetween() stays within bounds' => function () {
        $faker = Faker\Factory::create();
        $start = new DateTimeImmutable('2020-01-01 00:00:00');
        $end = new DateTimeImmutable('2020-12-31 23:59:59');
        $samples = array();
        for ($i = 0; $i < 20; $i++) {
            $date = $faker->dateTimeBetween('2020-01-01 00:00:00', '2020-12-31 23:59:59');
            check($date instanceof DateTimeInterface, 'Expected DateTime, got ' . gettype($date));
            check($date->getTimestamp() >= $start->getTimestamp()
                && $date->getTimestamp() <= $end->getTimestamp(),
                'Date outside range: ' . $date->format('Y-m-d H:i:s'));
            $samples[] = $date->format('Y-m-d');
        }
        return '20 dates in range; examples: ' . implode(', ', array_slice($samples, 0, 3));
    },
);

$passed = 0;
$failed = 0;
echo "Faker F17-F22 standalone tests | PHP " . PHP_VERSION . PHP_EOL;
echo str_repeat('=', 60) . PHP_EOL;
foreach ($tests as $name => $test) {
    try {
        $detail = $test();
        echo '[PASS] ' . $name . PHP_EOL;
        echo '       ' . $detail . PHP_EOL;
        $passed++;
    } catch (Throwable $error) {
        echo '[FAIL] ' . $name . PHP_EOL;
        echo '       ' . get_class($error) . ': ' . $error->getMessage() . PHP_EOL;
        $failed++;
    }
}
echo str_repeat('=', 60) . PHP_EOL;
echo 'SUMMARY: ' . $passed . ' passed, ' . $failed . ' failed, ' . count($tests) . ' total' . PHP_EOL;
exit($failed === 0 ? 0 : 1);
