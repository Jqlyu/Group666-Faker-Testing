<?php

declare(strict_types=1);

namespace Group666\MemberMacos;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;

/**
 * Provisional black-box system tests for unmodified fzaninotto/Faker v1.9.2.
 * Each public test method is one independent case; loops are internal samples.
 */
final class MacOsFunctionalTest extends TestCase
{
    public function testF01PersonNamesAndSeedReplay(): void
    {
        $faker = $this->generator('en_US', 740001);
        $names = $this->generateNames($faker);

        foreach ($names as $index => $name) {
            $this->assertIsString($name, 'F01 sample ' . $index);
            $this->assertNotSame('', trim($name));
            $this->assertSame(trim($name), $name);
            $this->assertMatchesRegularExpression("/\A[\p{L}][\p{L}\p{M} .'\x{2019}-]*\z/u", $name);
            $this->assertStringContainsString(' ', $name, 'Full name must contain name components.');
        }

        // Replay the same public calls after resetting the random seed.
        $replayed = $this->generateNames($this->generator('en_US', 740001));
        $this->assertSame($names, $replayed, 'Same seed and call order must replay names.');
        $this->observe('F01', 740001, 'en_US', array(
            'gender_inputs' => array(null, 'male', 'female'),
            'generated_names' => count($names),
            'first_samples' => array_slice($names, 0, 6),
            'replay_equal' => $names === $replayed,
        ));
    }

    public function testF02SafeEmailsUseReservedDomains(): void
    {
        $faker = $this->generator('en_US', 740002);
        $samples = array();
        $allowedDomains = array('example.com', 'example.org', 'example.net');

        for ($index = 0; $index < 100; ++$index) {
            $email = $faker->safeEmail();
            $this->assertIsString($email, 'F02 sample ' . $index);
            $this->assertNotFalse(filter_var($email, FILTER_VALIDATE_EMAIL));
            $this->assertSame(0, preg_match('/\s/u', $email));
            $domain = substr($email, strrpos($email, '@') + 1);
            $this->assertContains($domain, $allowedDomains);
            $samples[] = $email;
        }

        $this->observe('F02', 740002, 'en_US', array(
            'generated_emails' => count($samples),
            'first_samples' => array_slice($samples, 0, 6),
        ));
    }

    public function testF03BoundedDatesAndInvalidOrder(): void
    {
        $faker = $this->generator('en_US', 740003);
        $start = new \DateTime('2020-02-28 00:00:00', new \DateTimeZone('UTC'));
        $end = new \DateTime('2020-03-01 23:59:59', new \DateTimeZone('UTC'));
        $samples = array();

        for ($index = 0; $index < 100; ++$index) {
            $date = $faker->dateTimeBetween($start, $end, 'UTC');
            $this->assertInstanceOf(\DateTime::class, $date, 'F03 sample ' . $index);
            $this->assertGreaterThanOrEqual($start->getTimestamp(), $date->getTimestamp());
            $this->assertLessThanOrEqual($end->getTimestamp(), $date->getTimestamp());
            $this->assertSame('UTC', $date->getTimezone()->getName());
            $samples[] = $date->format(DATE_ATOM);
        }

        $singleton = $faker->dateTimeBetween($start, $start, 'UTC');
        $this->assertSame($start->getTimestamp(), $singleton->getTimestamp());
        $this->assertSame('UTC', $singleton->getTimezone()->getName());

        $exception = null;
        try {
            $faker->dateTimeBetween($end, $start, 'UTC');
        } catch (\InvalidArgumentException $caught) {
            $exception = $caught;
        }
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception, 'Reversed dates must be rejected.');

        $this->observe('F03', 740003, 'en_US', array(
            'timezone' => 'UTC',
            'start' => $start->format(DATE_ATOM),
            'end' => $end->format(DATE_ATOM),
            'bounded_dates' => count($samples),
            'first_samples' => array_slice($samples, 0, 6),
            'singleton' => $singleton->format(DATE_ATOM),
            'reversed_exception' => get_class($exception),
            'reversed_message' => $exception->getMessage(),
        ));
    }

    public function testF04IntegerRangeBoundariesAndReversedBounds(): void
    {
        $faker = $this->generator('en_US', 740004);
        $partitions = array(
            array(-100, 100),
            array(10, -10),
            array(0, 1),
            array(-7, -7),
            array(0, 0),
            array(1, 1),
            // Keep the minimum an integer even on 32-bit PHP parsers.
            array(-2147483647 - 1, -2147483647 - 1),
            array(2147483647, 2147483647),
        );
        $observed = array();

        foreach ($partitions as $bounds) {
            $values = array();
            $count = $bounds[0] === $bounds[1] ? 1 : 50;
            for ($index = 0; $index < $count; ++$index) {
                $value = $faker->numberBetween($bounds[0], $bounds[1]);
                $this->assertIsInt($value, 'F04 bounds ' . implode(',', $bounds));
                $this->assertGreaterThanOrEqual(min($bounds), $value);
                $this->assertLessThanOrEqual(max($bounds), $value);
                if ($bounds[0] === $bounds[1]) {
                    $this->assertSame($bounds[0], $value);
                }
                $values[] = $value;
            }
            $observed[] = array(
                'bounds' => $bounds,
                'sample_count' => count($values),
                'observed_min' => min($values),
                'observed_max' => max($values),
                'first_samples' => array_slice($values, 0, 6),
            );
        }

        $this->observe('F04', 740004, 'en_US', array('partitions' => $observed));
    }

    public function testF05FinnishAddressLocale(): void
    {
        $faker = $this->generator('fi_FI', 740005);
        // This pinned locale-data oracle detects accidental en_US fallback.
        $finnishStates = array(
            'Ahvenanmaa', 'Etelä-Karjala', 'Etelä-Pohjanmaa', 'Etelä-Savo',
            'Kainuu', 'Kanta-Häme', 'Keski-Pohjanmaa', 'Keski-Suomi',
            'Kymenlaakso', 'Lappi', 'Pirkanmaa', 'Pohjanmaa',
            'Pohjois-Karjala', 'Pohjois-Pohjanmaa', 'Pohjois-Savo',
            'Päijät-Häme', 'Satakunta', 'Uusimaa', 'Varsinais-Suomi',
        );
        $samples = array();

        for ($index = 0; $index < 100; ++$index) {
            $postcode = $faker->postcode();
            $state = $faker->state();
            $this->assertIsString($postcode, 'F05 sample ' . $index);
            $this->assertMatchesRegularExpression('/\A[0-9]{5}\z/', $postcode);
            $this->assertIsString($state);
            $this->assertContains($state, $finnishStates);
            $samples[] = array('postcode' => $postcode, 'state' => $state);
        }

        $this->observe('F05', 740005, 'fi_FI', array(
            'generated_pairs' => count($samples),
            'first_samples' => array_slice($samples, 0, 6),
        ));
    }

    public function testF06UuidVersionVariantAndSampleDiversity(): void
    {
        $faker = $this->generator('en_US', 740006);
        $samples = array();

        for ($index = 0; $index < 100; ++$index) {
            $uuid = $faker->uuid();
            $this->assertIsString($uuid, 'F06 sample ' . $index);
            // Faker v1.9.2 documents version 3, not version 4.
            $this->assertMatchesRegularExpression(
                '/\A[0-9a-f]{8}-[0-9a-f]{4}-3[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\z/',
                $uuid
            );
            $samples[] = $uuid;
        }
        // This finite fixed-seed check is not a guarantee of global uniqueness.
        $this->assertCount(100, array_unique($samples));

        $this->observe('F06', 740006, 'en_US', array(
            'generated_uuids' => count($samples),
            'distinct_in_sample' => count(array_unique($samples)),
            'first_samples' => array_slice($samples, 0, 6),
        ));
    }

    private function generator(string $locale, int $seed): Generator
    {
        $faker = Factory::create($locale);
        $faker->seed($seed);

        return $faker;
    }

    private function generateNames(Generator $faker): array
    {
        $names = array();
        foreach (array(null, 'male', 'female') as $gender) {
            for ($index = 0; $index < 20; ++$index) {
                $names[] = $faker->name($gender);
            }
        }

        return $names;
    }

    /** Save only real observations, when the runner explicitly requests them. */
    private function observe(string $id, int $seed, string $locale, array $observations): void
    {
        $directory = getenv('FAKER_TEST_EVIDENCE_DIR');
        if ($directory === false || $directory === '') {
            return;
        }
        $json = json_encode(array(
            'test_id' => $id,
            'seed' => $seed,
            'locale' => $locale,
            'observations' => $observations,
        ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $written = file_put_contents(rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $id . '.json', $json . PHP_EOL);
        if ($written === false) {
            throw new \RuntimeException('Cannot write requested observation evidence for ' . $id);
        }
    }
}
