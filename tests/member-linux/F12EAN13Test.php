<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class F12EAN13Test extends TestCase
{
    public function testF12EAN13Generation(): void
    {
        // Arrange: Create Faker generator
        $faker = Faker\Factory::create('en_US');

        // Fixed seed for reproducible testing
        $faker->seed(740012);

        // Generate 100 EAN-13 barcodes
        for ($i = 0; $i < 100; $i++) {

            // Act
            $barcode = $faker->ean13();

            // Assert 1: Output must be a string
            $this->assertIsString(
                $barcode,
                'F12: Barcode must be a string.'
            );

            // Assert 2: Exactly 13 numeric digits
            $this->assertMatchesRegularExpression(
                '/\A[0-9]{13}\z/',
                $barcode,
                'F12: Invalid EAN-13 format: ' . $barcode
            );

            // Assert 3: Valid checksum
            $this->assertTrue(
                $this->isValidEAN13($barcode),
                'F12: Invalid EAN-13 checksum: ' . $barcode
            );
        }
    }

    public function testF12ChecksumValidation(): void
    {
        // Known valid EAN-13 barcode
        $validBarcode = '4006381333931';

        // Same barcode with incorrect check digit
        $invalidBarcode = '4006381333932';

        // Assert: Valid barcode should pass
        $this->assertTrue(
            $this->isValidEAN13($validBarcode)
        );

        // Assert: Invalid barcode should fail
        $this->assertFalse(
            $this->isValidEAN13($invalidBarcode)
        );
    }

    private function isValidEAN13(string $barcode): bool
    {
        // Check barcode format
        if (!preg_match('/\A[0-9]{13}\z/', $barcode)) {
            return false;
        }

        $sum = 0;

        // Calculate weighted sum of all 13 digits
        for ($i = 0; $i < 13; $i++) {

            $digit = (int) $barcode[$i];

            // EAN-13 weights alternate between 1 and 3
            $weight = ($i % 2 === 0) ? 1 : 3;

            $sum += $digit * $weight;
        }

        // Valid EAN-13 must be divisible by 10
        return $sum % 10 === 0;
    }
}
