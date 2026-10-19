<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class F11LoremParagraphsTest extends TestCase
{
    public function testF11ArrayModeCountAndStructure(): void
    {
        // Arrange
        $faker = Faker\Factory::create('en_US');
        $faker->seed(740011);

        // Part A: Zero paragraphs
        $this->assertSame(
            [],
            $faker->paragraphs(0, false)
        );

        // Part B: Paragraph count boundaries
        $counts = [1, 3, 5];

        foreach ($counts as $count) {

            for ($i = 0; $i < 10; $i++) {

                // Act
                $paragraphs = $faker->paragraphs(
                    $count,
                    false
                );

                // Assert: Array output
                $this->assertIsArray($paragraphs);

                // Assert: Correct paragraph count
                $this->assertCount(
                    $count,
                    $paragraphs
                );

                foreach ($paragraphs as $paragraph) {

                    // Paragraph must be a string
                    $this->assertIsString($paragraph);

                    // Paragraph must not be empty
                    $this->assertNotSame(
                        '',
                        trim($paragraph)
                    );

                    // Paragraph must end with a period
                    $this->assertMatchesRegularExpression(
                        '/\.\z/',
                        $paragraph
                    );
                }
            }
        }
    }

    public function testF11TextModeFormatting(): void
    {
        // Arrange
        $faker = Faker\Factory::create('en_US');
        $faker->seed(740011);

        // Part C: Zero paragraphs in text mode
        $this->assertSame(
            '',
            $faker->paragraphs(0, true)
        );

        // Part D: Text output with three paragraphs
        for ($i = 0; $i < 10; $i++) {

            // Act
            $text = $faker->paragraphs(3, true);

            // Assert: Output must be a string
            $this->assertIsString($text);

            // Exactly two paragraph separators
            $this->assertSame(
                2,
                substr_count($text, "\n\n")
            );

            // Split text into paragraphs
            $parts = explode("\n\n", $text);

            // Exactly three paragraphs
            $this->assertCount(3, $parts);

            foreach ($parts as $paragraph) {

                // Each paragraph must not be empty
                $this->assertNotSame(
                    '',
                    trim($paragraph)
                );

                // Each paragraph must end with a period
                $this->assertMatchesRegularExpression(
                    '/\.\z/',
                    $paragraph
                );
            }
        }
    }
}
