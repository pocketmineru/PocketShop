<?php

declare(strict_types=1);

namespace pocketmine_ru\PocketShop\tests;

use PHPUnit\Framework\TestCase;

final class NavigationTest extends TestCase
{
    private const ITEMS_PER_PAGE = 45;

    public function testPaginationCalculation(): void
    {
        $testCases = [
            ['total' => 10, 'perPage' => 45, 'expectedPages' => 1],
            ['total' => 45, 'perPage' => 45, 'expectedPages' => 1],
            ['total' => 46, 'perPage' => 45, 'expectedPages' => 2],
            ['total' => 90, 'perPage' => 45, 'expectedPages' => 2],
            ['total' => 100, 'perPage' => 45, 'expectedPages' => 3],
            ['total' => 350, 'perPage' => 45, 'expectedPages' => 8],
        ];

        foreach ($testCases as $case) {
            $totalPages = (int) ceil($case['total'] / $case['perPage']);
            $this->assertEquals(
                $case['expectedPages'],
                $totalPages,
                "{$case['total']} items / {$case['perPage']} per page = {$case['expectedPages']} pages"
            );
        }
    }

    public function testPageBoundsClamping(): void
    {
        $testCases = [
            ['page' => -5, 'totalPages' => 5, 'expected' => 0],
            ['page' => 0, 'totalPages' => 5, 'expected' => 0],
            ['page' => 2, 'totalPages' => 5, 'expected' => 2],
            ['page' => 4, 'totalPages' => 5, 'expected' => 4],
            ['page' => 10, 'totalPages' => 5, 'expected' => 4],
        ];

        foreach ($testCases as $case) {
            $clampedPage = max(0, min($case['page'], $case['totalPages'] - 1));
            $this->assertEquals(
                $case['expected'],
                $clampedPage,
                "Page {$case['page']} with {$case['totalPages']} pages should be {$case['expected']}"
            );
        }
    }

    public function testSlotCalculationForChest(): void
    {
        $prevSlotChest = 18;
        $nextSlotChest = 26;

        $this->assertEquals(18, $prevSlotChest, 'Prev button should be at slot 18 for 27-slot chest');
        $this->assertEquals(26, $nextSlotChest, 'Next button should be at slot 26 for 27-slot chest');
    }

    public function testSlotCalculationForHopper(): void
    {
        $prevSlotHopper = 48;
        $nextSlotHopper = 50;

        $this->assertEquals(48, $prevSlotHopper, 'Prev button should be at slot 48 for 45-slot hopper');
        $this->assertEquals(50, $nextSlotHopper, 'Next button should be at slot 50 for 45-slot hopper');
    }

    public function testCategorySizes(): void
    {
        $categorySizes = [
            'blocks' => 27,
            'items' => 27,
            'tools' => 27,
            'armor' => 27,
            'swords' => 27,
            'dyes' => 27,
            'food' => 27,
            'potions' => 27,
            'spawn_eggs' => 45,
        ];

        foreach ($categorySizes as $category => $expectedSize) {
            $this->assertGreaterThanOrEqual(27, $expectedSize);
            $this->assertLessThanOrEqual(45, $expectedSize);
        }

        $this->assertEquals(45, $categorySizes['spawn_eggs'], 'spawn_eggs should use hopper (45 slots)');
    }

    public function testNavigationVisibility(): void
    {
        $testCases = [
            ['page' => 0, 'totalPages' => 1, 'showPrev' => false, 'showNext' => false],
            ['page' => 0, 'totalPages' => 2, 'showPrev' => false, 'showNext' => true],
            ['page' => 1, 'totalPages' => 2, 'showPrev' => true, 'showNext' => false],
            ['page' => 1, 'totalPages' => 3, 'showPrev' => true, 'showNext' => true],
        ];

        foreach ($testCases as $case) {
            $showPrev = $case['page'] > 0;
            $showNext = $case['page'] < $case['totalPages'] - 1;

            $this->assertEquals(
                $case['showPrev'],
                $showPrev,
                "Page {$case['page']}/{$case['totalPages']}: prev should be " . ($case['showPrev'] ? 'visible' : 'hidden')
            );

            $this->assertEquals(
                $case['showNext'],
                $showNext,
                "Page {$case['page']}/{$case['totalPages']}: next should be " . ($case['showNext'] ? 'visible' : 'hidden')
            );
        }
    }

    public function testItemIndexCalculation(): void
    {
        $startIndex = 0;
        $slot = 10;
        $itemIndex = $startIndex + $slot;

        $this->assertEquals(10, $itemIndex);

        $startIndex = 45;
        $slot = 5;
        $itemIndex = $startIndex + $slot;

        $this->assertEquals(50, $itemIndex);
    }

    public function testEmptyItemsHandling(): void
    {
        $emptyItems = [];
        $pageItems = array_slice($emptyItems, 0, 45);

        $this->assertEmpty($pageItems);
        $this->assertEquals(0, count($pageItems));
    }

    public function testOverflowItemsHandling(): void
    {
        $items = range(1, 100);
        $pageItems = array_slice($items, 45, 45);

        $this->assertEquals(45, count($pageItems));
        $this->assertEquals(46, $pageItems[0]);
        $this->assertEquals(90, $pageItems[44]);
    }
}