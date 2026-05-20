<?php

declare(strict_types=1);

namespace Jmf\Twig\Extension\Sort;

use Jmf\Sort\Direction;
use Jmf\Sort\PropertyPass;
use Jmf\Twig\Extension\Sort\Exception\SortException;
use Traversable;

class PropertyPassParser
{
    /**
     * @param string|list<string>|array<string, string>|Traversable<string, string> $specs
     *
     * @return PropertyPass[]
     *
     * @throws SortException
     */
    public function parse(
        iterable | string $specs,
    ): iterable {
        if (is_string($specs)) {
            return [
                new PropertyPass(
                    property: $specs,
                ),
            ];
        }

        if ($specs instanceof Traversable) {
            $specs = iterator_to_array($specs);
        }

        $propertyPasses = [];

        foreach ($specs as $property => $propertySpecs) {
            $propertyPasses[] = $this->parsePropertySpecs($property, $propertySpecs);
        }

        return $propertyPasses;
    }

    /**
     * @param string|array<string, mixed> $propertySpecs
     *
     * @throws SortException
     */
    private function parsePropertySpecs(
        int | string $property,
        array | string $propertySpecs,
    ): PropertyPass {
        return new PropertyPass(
            $this->parseProperty($property, $propertySpecs),
            $this->parseDirection($property, $propertySpecs),
            $this->parseFlags($property, $propertySpecs),
        );
    }

    /**
     * @param string|array<string, mixed> $propertySpecs
     *
     * @throws SortException
     */
    private function parseProperty(
        int | string $property,
        array | string $propertySpecs,
    ): string {
        if (is_numeric($property)) {
            if (!is_string($propertySpecs)) {
                throw new SortException();
            }

            return $propertySpecs;
        }

        return $property;
    }

    /**
     * @param string|array<string, mixed> $propertySpecs
     *
     * @throws SortException
     */
    private function parseDirection(
        int | string $property,
        array | string $propertySpecs,
    ): Direction {
        if (is_numeric($property)) {
            return Direction::ASC;
        }

        if (is_string($propertySpecs)) {
            return $this->translateDirection($propertySpecs);
        }

        if (array_key_exists('direction', $propertySpecs)) {
            if (!is_string($propertySpecs['direction'])) {
                throw new SortException("Property specs 'direction' should be either 'asc' or 'desc'.");
            }

            return $this->translateDirection($propertySpecs['direction']);
        }

        return Direction::ASC;
    }

    /**
     * @throws SortException
     */
    private function translateDirection(
        string $direction,
    ): Direction {
        return match ($direction) {
            'asc'   => Direction::ASC,
            'desc'  => Direction::DESC,
            default => throw new SortException("Property specs 'direction' should be either 'asc' or 'desc'."),
        };
    }

    /**
     * @param string|array<string, mixed> $propertySpecs
     *
     * @throws SortException
     */
    private function parseFlags(
        int | string $property,
        array | string $propertySpecs,
    ): int {
        if (is_numeric($property) || !is_array($propertySpecs)) {
            return 0;
        }

        $flags = 0;

        if (array_key_exists('flags', $propertySpecs)) {
            if (!is_array($propertySpecs['flags'])) {
                throw new SortException("Property specs 'flags' should be an array of integers.");
            }

            foreach ($propertySpecs['flags'] as $flag) {
                if (!is_int($flag)) {
                    throw new SortException("Property specs 'flags' should be an array of integers.");
                }

                $flags += $flag;
            }
        }

        return $flags;
    }
}