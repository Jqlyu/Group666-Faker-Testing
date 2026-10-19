<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class F10CreditCardTest extends TestCase
{
    public function testF10CreditCardNumberValidation(): void
    {
        // Arrange
        $faker = Faker\Factory::create('en_US');
        $faker->seed(740010);

        // Supported card types and expected formats
        $cardTypes = [
            'Visa' => '/\A4[0-9]{15}\z/',

            'MasterCard' =>
                '/\A(?:5[1-5]|2[2-7])[0-9]{14}\z/',

            'American Express' =>
                '/\A3[47][0-9]{13}\z/',

            'Discover Card' =>
                '/\A6011[0-9]{12}\z/',
        ];

        foreach ($cardTypes as $type => $pattern) {

            for ($i = 0; $i < 25; $i++) {

                // Act
                $number = $faker->creditCardNumber(
                    $type,
                    false
                );

                // Assert 1: Output must be a string
                $this->assertIsString(
                    $number,
                    'F10: Card number must be a string.'
                );

                // Assert 2: Check card format
                $this->assertMatchesRegularExpression(
                    $pattern,
                    $number,
                    'F10: Invalid format for ' . $type
                );

                // Assert 3: Validate Luhn checksum
                $this->assertTrue(
                    $this->isValidLuhn($number),
                    'F10: Invalid Luhn checksum for ' . $type
                );
            }
        }
    }

    private function isValidLuhn(string $number): bool
    {
        // Reject empty or non-numeric input
        if ($number === '' || !ctype_digit($number)) {
            return false;
        }

        $sum = 0;
        $double = false;

        // Calculate checksum from right to left
        for ($i = strlen($number) - 1; $i >= 0; $i--) {

            $digit = (int) $number[$i];

            if ($double) {
                $digit *= 2;

                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $double = !$double;
        }

        return $sum % 10 === 0;
    }
}
