<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class F09InternetIPTest extends TestCase
{
    public function testF09IPv4AndIPv6Generation(): void
    {
        // Arrange
        $faker = Faker\Factory::create('en_US');

        // Fixed seed for reproducibility
        $faker->seed(740009);

        // Part A: IPv4 generation
        for ($i = 0; $i < 100; $i++) {

            // Act
            $ipv4 = $faker->ipv4();

            // Assert: Must return a string
            $this->assertIsString(
                $ipv4,
                'F09-A: IPv4 result must be a string.'
            );

            // Assert: Must be a valid IPv4 address
            $this->assertNotFalse(
                filter_var(
                    $ipv4,
                    FILTER_VALIDATE_IP,
                    FILTER_FLAG_IPV4
                ),
                'F09-A: Invalid IPv4 address: ' . $ipv4
            );
        }

        // Part B: IPv6 generation
        for ($i = 0; $i < 100; $i++) {

            // Act
            $ipv6 = $faker->ipv6();

            // Assert: Must return a string
            $this->assertIsString(
                $ipv6,
                'F09-B: IPv6 result must be a string.'
            );

            // Assert: Must be a valid IPv6 address
            $this->assertNotFalse(
                filter_var(
                    $ipv6,
                    FILTER_VALIDATE_IP,
                    FILTER_FLAG_IPV6
                ),
                'F09-B: Invalid IPv6 address: ' . $ipv6
            );
        }
    }
}
