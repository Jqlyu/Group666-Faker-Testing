<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class F07RegexifyTest extends TestCase
{
    public function testF07RegexifyMatchesSupportedPatterns(): void
    {
        // Arrange
        $faker = Faker\Factory::create('en_US');

        // Fixed seed for reproducible execution
        $faker->seed(740007);

        // Partition A: Fixed-length pattern
        for ($i = 0; $i < 50; $i++) {

            $result = $faker->regexify('[A-Z]{2}[0-9]{3}');

            $this->assertMatchesRegularExpression(
                '/^[A-Z]{2}[0-9]{3}$/D',
                $result,
                'F07-A: Generated string does not match the pattern.'
            );
        }

        // Partition B: Alternation pattern
        for ($i = 0; $i < 50; $i++) {

            $result = $faker->regexify('(cat|dog)-[0-9]{2}');

            $this->assertMatchesRegularExpression(
                '/^(cat|dog)-[0-9]{2}$/D',
                $result,
                'F07-B: Generated string does not match the pattern.'
            );
        }

        // Partition C: Variable-length pattern
        for ($i = 0; $i < 50; $i++) {

            $result = $faker->regexify('[A-F]{1,4}');

            $this->assertMatchesRegularExpression(
                '/^[A-F]{1,4}$/D',
                $result,
                'F07-C: Generated string does not match the pattern.'
            );
        }
    }
}
