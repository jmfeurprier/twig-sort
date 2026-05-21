<?php

declare(strict_types=1);

namespace Jmf\Twig\Extension\Sort;

use Jmf\Sort\AssociativeSorter;
use Jmf\Sort\ByKeySorter;
use Jmf\Sort\ByPropertySorter;
use Jmf\Sort\ByValueSorter;
use Jmf\Sort\Direction;
use Jmf\Twig\Extension\Sort\Exception\SortException;
use Override;
use Traversable;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SortExtension extends AbstractExtension
{
    public final const string PREFIX_DEFAULT = '';

    public function __construct(
        private readonly ByPropertySorter $byPropertySorter = new ByPropertySorter(),
        private readonly ByKeySorter $byKeySorter = new ByKeySorter(),
        private readonly ByValueSorter $byValueSorter = new ByValueSorter(),
        private readonly AssociativeSorter $associativeSorter = new AssociativeSorter(),
        private readonly PropertyPassParser $propertyPassParser = new PropertyPassParser(),
        private readonly string $functionPrefix = self::PREFIX_DEFAULT,
    ) {
    }

    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                "{$this->functionPrefix}sort",
                $this->sort(...),
            ),
            new TwigFilter(
                "{$this->functionPrefix}rsort",
                $this->rsort(...),
            ),
            new TwigFilter(
                "{$this->functionPrefix}asort",
                $this->asort(...),
            ),
            new TwigFilter(
                "{$this->functionPrefix}arsort",
                $this->arsort(...),
            ),
            new TwigFilter(
                "{$this->functionPrefix}ksort",
                $this->ksort(...),
            ),
            new TwigFilter(
                "{$this->functionPrefix}krsort",
                $this->krsort(...),
            ),
            new TwigFilter(
                "{$this->functionPrefix}psort",
                $this->psort(...),
            ),
        ];
    }

    /**
     * @param iterable<mixed> $array
     *
     * @return iterable<mixed>
     */
    public function sort(
        iterable $array,
        int $flags = SORT_REGULAR,
    ): iterable {
        return $this->byValueSorter->sort($array, Direction::ASC, $flags);
    }

    /**
     * @param iterable<mixed> $array
     *
     * @return iterable<mixed>
     */
    public function rsort(
        iterable $array,
        int $flags = SORT_REGULAR,
    ): iterable {
        return $this->byValueSorter->rsort($array, $flags);
    }

    /**
     * @param array<int|string, mixed> $array
     *
     * @return array<int|string, mixed>
     */
    public function asort(
        array $array,
        int $flags = SORT_REGULAR,
    ): array {
        return $this->associativeSorter->sort($array, Direction::ASC, $flags);
    }

    /**
     * @param array<int|string, mixed> $array
     *
     * @return array<int|string, mixed>
     */
    public function arsort(
        array $array,
        int $flags = SORT_REGULAR,
    ): array {
        return $this->associativeSorter->rsort($array, $flags);
    }

    /**
     * @param array<int|string, mixed> $array
     *
     * @return array<int|string, mixed>
     */
    public function ksort(
        array $array,
        int $flags = SORT_REGULAR,
    ): array {
        return $this->byKeySorter->sort($array, Direction::ASC, $flags);
    }

    /**
     * @param array<int|string, mixed> $array
     *
     * @return array<int|string, mixed>
     */
    public function krsort(
        array $array,
        int $flags = SORT_REGULAR,
    ): array {
        return $this->byKeySorter->rsort($array, $flags);
    }

    /**
     * Sorts arrays of arrays and arrays of objects by properties.
     *
     * Possible usages:
     * - {{ articles|psort('title') }}
     * - {{ articles|psort(['title', 'author.name']) }}
     * - {{ articles|psort({'publication_date': 'desc', 'author': 'asc'}) }}
     *
     * @param array<int|string, array<string, mixed>|object> $array
     * @param string|list<string>|array<string, string>|Traversable<string, string> $specs
     *
     * @return array<int|string, array<string, mixed>|object>
     *
     * @throws SortException
     */
    public function psort(
        array $array,
        iterable | string $specs,
    ): array {
        return $this->byPropertySorter->sort(
            $array,
            $this->propertyPassParser->parse($specs),
        );
    }
}
