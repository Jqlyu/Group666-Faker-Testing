<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class F08RandomElementsTest extends TestCase
{
    public function testF08SelectionAndBoundaries(): void
    {
        // Arrange
        $faker = Faker\Factory::create('en_US');
        $faker->seed(740008);

        $source = ['A', 'B', 'C', 'D', 'E'];

        // Part A: Normal selection without duplicates
        for ($i = 0; $i < 30; $i++) {

            $result = $faker->randomElements(
                $source,
                3,
                false
            );

            // Result must be an array
            $this->assertIsArray($result);

            // Exactly 3 elements must be returned
            $this->assertCount(3, $result);

            // All selected elements must be unique
            $this->assertCount(
                3,
                array_unique($result)
            );

            // Every element must belong to the source
            foreach ($result as $element) {
                $this->assertContains(
                    $element,
                    $source
                );
            }
        }

        // Part B: Request zero elements
        $emptyResult = $faker->randomElements(
            $source,
            0,
            false
        );

        $this->assertSame([], $emptyResult);

        // Part C: Request all available elements
        $allResult = $faker->randomElements(
            $source,
            5,
            false
        );

        $this->assertCount(5, $allResult);

        $this->assertEqualsCanonicalizing(
            $source,
            $allResult
        );

        // Part D: Allow repeated selections
        $duplicatesAllowed = $faker->randomElements(
            ['A', 'B'],
            6,
            true
        );

        $this->assertCount(
            6,
            $duplicatesAllowed
        );

        foreach ($duplicatesAllowed as $element) {
            $this->assertContains(
                $element,
                ['A', 'B']
            );
        }
    }

    public function testF08OversizedSelectionThrowsLengthException(): void
    {
        // Arrange
        $faker = Faker\Factory::create('en_US');

        $source = ['A', 'B', 'C', 'D', 'E'];

        // Assert expected exception
        $this->expectException(
            \LengthException::class
        );

        $this->expectExceptionMessage(
            'Cannot get 6 elements, only 5 in array'
        );

        // Act: Request more unique elements than available
        $faker->randomElements(
            $source,
            6,
            false
        );
    }
}
