<?php

declare(strict_types=1);

namespace Jmf\Twig\Extension\Sort;

use Jmf\Sort\AssociativeSorter;
use Jmf\Sort\ByKeySorter;
use Jmf\Sort\ByPropertySorter;
use Jmf\Sort\ByValueSorter;
use Override;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Twig\TwigFilter;

final class SortExtensionTest extends TestCase
{
    private SortExtension $sortExtension;

    #[Override]
    protected function setUp(): void
    {
        $associativeSorter = new AssociativeSorter();

        $this->sortExtension = new SortExtension(
            new ByPropertySorter(new PropertyAccessor(), $associativeSorter),
            new ByKeySorter(),
            new ByValueSorter(),
            $associativeSorter,
            new PropertyPassParser(),
        );
    }

    public function testGetFiltersReturnsExpectedFilters(): void
    {
        $filters = $this->sortExtension->getFilters();

        $this->assertContainsOnlyInstancesOf(TwigFilter::class, $filters);
        $this->assertCount(7, $filters);

        $names = array_map(
            static fn(
                TwigFilter $twigFilter,
            ): string => $twigFilter->getName(),
            $filters,
        );

        $this->assertSame(
            [
                'sort',
                'rsort',
                'asort',
                'arsort',
                'ksort',
                'krsort',
                'psort',
            ],
            $names,
        );
    }

    public function testGetFiltersWithPrefixPrependsPrefixToNames(): void
    {
        $associativeSorter = new AssociativeSorter();
        $sortExtension         = new SortExtension(
            new ByPropertySorter(new PropertyAccessor(), $associativeSorter),
            new ByKeySorter(),
            new ByValueSorter(),
            $associativeSorter,
            new PropertyPassParser(),
            'jmf_',
        );

        $names = array_map(
            static fn(
                TwigFilter $twigFilter,
            ): string => $twigFilter->getName(),
            $sortExtension->getFilters(),
        );

        $this->assertSame(
            [
                'jmf_sort',
                'jmf_rsort',
                'jmf_asort',
                'jmf_arsort',
                'jmf_ksort',
                'jmf_krsort',
                'jmf_psort',
            ],
            $names,
        );
    }

    public function testSortSortsValuesAscending(): void
    {
        $this->assertSame(
            [
                1,
                2,
                3,
            ],
            array_values(
                (array) ($this->sortExtension->sort(
                    [
                        3,
                        1,
                        2,
                    ],
                )),
            ),
        );
    }

    public function testSortWithEmptyArray(): void
    {
        $this->assertSame(
            [],
            (array) $this->sortExtension->sort([]),
        );
    }

    public function testRsortSortsValuesDescending(): void
    {
        $this->assertSame(
            [
                3,
                2,
                1,
            ],
            array_values(
                (array) $this->sortExtension->rsort(
                    [
                        3,
                        1,
                        2,
                    ],
                ),
            ),
        );
    }

    public function testAsortSortsValuesAscendingPreservingKeys(): void
    {
        $this->assertSame(
            [
                'a' => 1,
                'c' => 2,
                'b' => 3,
            ],
            $this->sortExtension->asort(
                [
                    'b' => 3,
                    'a' => 1,
                    'c' => 2,
                ],
            ),
        );
    }

    public function testArsortSortsValuesDescendingPreservingKeys(): void
    {
        $this->assertSame(
            [
                'b' => 3,
                'c' => 2,
                'a' => 1,
            ],
            $this->sortExtension->arsort(
                [
                    'b' => 3,
                    'a' => 1,
                    'c' => 2,
                ],
            ),
        );
    }

    public function testKsortSortsByKeyAscending(): void
    {
        $this->assertSame(
            [
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ],
            $this->sortExtension->ksort(
                [
                    'c' => 3,
                    'a' => 1,
                    'b' => 2,
                ],
            ),
        );
    }

    public function testKrsortSortsByKeyDescending(): void
    {
        $this->assertSame(
            [
                'c' => 3,
                'b' => 2,
                'a' => 1,
            ],
            $this->sortExtension->krsort(
                [
                    'c' => 3,
                    'a' => 1,
                    'b' => 2,
                ],
            ),
        );
    }

    public function testPsortWithStringSpec(): void
    {
        $this->assertSame(
            [
                1 => ['name' => 'Alice'],
                2 => ['name' => 'Bob'],
                0 => ['name' => 'Charlie'],
            ],
            $this->sortExtension->psort(
                [
                    ['name' => 'Charlie'],
                    ['name' => 'Alice'],
                    ['name' => 'Bob'],
                ],
                '[name]',
            ),
        );
    }

    public function testPsortWithIndexedArraySpec(): void
    {
        $this->assertSame(
            [
                2 => [
                    'name' => 'Alice',
                    'age'  => 20,
                ],
                1 => [
                    'name' => 'Alice',
                    'age'  => 25,
                ],
                0 => [
                    'name' => 'Charlie',
                    'age'  => 30,
                ],
            ],
            $this->sortExtension->psort(
                [
                    [
                        'name' => 'Charlie',
                        'age'  => 30,
                    ],
                    [
                        'name' => 'Alice',
                        'age'  => 25,
                    ],
                    [
                        'name' => 'Alice',
                        'age'  => 20,
                    ],
                ],
                [
                    '[name]',
                    '[age]',
                ],
            ),
        );
    }

    public function testPsortWithAssociativeArraySpec(): void
    {
        $this->assertSame(
            [
                0 => ['name' => 'Charlie'],
                2 => ['name' => 'Bob'],
                1 => ['name' => 'Alice'],
            ],
            $this->sortExtension->psort(
                [
                    ['name' => 'Charlie'],
                    ['name' => 'Alice'],
                    ['name' => 'Bob'],
                ],
                ['[name]' => 'desc'],
            ),
        );
    }

    public function testPsortWithEmptyArray(): void
    {
        $this->assertSame(
            [],
            $this->sortExtension->psort(
                [],
                '[name]',
            ),
        );
    }
}