<?php

declare(strict_types=1);

namespace Jmf\Twig\Extension\Sort;

use ArrayIterator;
use Jmf\Sort\Direction;
use Jmf\Sort\PropertyPass;
use Jmf\Twig\Extension\Sort\Exception\SortException;
use Override;
use PHPUnit\Framework\TestCase;

final class PropertyPassParserTest extends TestCase
{
    private PropertyPassParser $propertyPassParser;

    #[Override]
    protected function setUp(): void
    {
        $this->propertyPassParser = new PropertyPassParser();
    }

    public function testParseStringReturnsAscPass(): void
    {
        $result = $this->propertyPassParser->parse('title');

        $this->assertEquals(
            [new PropertyPass('title', Direction::ASC)],
            $result,
        );
    }

    public function testParseIndexedArrayOfStrings(): void
    {
        $result = $this->propertyPassParser->parse(['title', 'author']);

        $this->assertEquals(
            [
                new PropertyPass('title', Direction::ASC),
                new PropertyPass('author', Direction::ASC),
            ],
            $result,
        );
    }

    public function testParseAssociativeArrayWithAscDirection(): void
    {
        $result = $this->propertyPassParser->parse(['title' => 'asc']);

        $this->assertEquals(
            [new PropertyPass('title', Direction::ASC)],
            $result,
        );
    }

    public function testParseAssociativeArrayWithDescDirection(): void
    {
        $result = $this->propertyPassParser->parse(['title' => 'desc']);

        $this->assertEquals(
            [new PropertyPass('title', Direction::DESC)],
            $result,
        );
    }

    public function testParseAssociativeArrayWithMultipleProperties(): void
    {
        $result = $this->propertyPassParser->parse([
            'published_at' => 'desc',
            'title'        => 'asc',
        ]);

        $this->assertEquals(
            [
                new PropertyPass('published_at', Direction::DESC),
                new PropertyPass('title', Direction::ASC),
            ],
            $result,
        );
    }

    public function testParseTraversable(): void
    {
        $result = $this->propertyPassParser->parse(new ArrayIterator(['published_at' => 'desc', 'title' => 'asc']));

        $this->assertEquals(
            [
                new PropertyPass('published_at', Direction::DESC),
                new PropertyPass('title', Direction::ASC),
            ],
            $result,
        );
    }

    public function testParseAssociativeArrayWithDirectionInSpecs(): void
    {
        // @phpstan-ignore argument.type
        $result = $this->propertyPassParser->parse(['title' => ['direction' => 'desc']]);

        $this->assertEquals(
            [new PropertyPass('title', Direction::DESC)],
            $result,
        );
    }

    public function testParseAssociativeArrayWithDefaultDirectionWhenNoDirectionKey(): void
    {
        // @phpstan-ignore argument.type
        $result = $this->propertyPassParser->parse(['title' => []]);

        $this->assertEquals(
            [new PropertyPass('title', Direction::ASC)],
            $result,
        );
    }

    public function testParseAssociativeArrayWithFlags(): void
    {
        // @phpstan-ignore argument.type
        $result = $this->propertyPassParser->parse([
            'title' => [
                'direction' => 'asc',
                'flags'     => [SORT_STRING, SORT_FLAG_CASE],
            ],
        ]);

        $this->assertEquals(
            [new PropertyPass('title', Direction::ASC, SORT_STRING | SORT_FLAG_CASE)],
            $result,
        );
    }

    public function testParseAssociativeArrayWithEmptyFlags(): void
    {
        // @phpstan-ignore argument.type
        $result = $this->propertyPassParser->parse(['title' => ['flags' => []]]);

        $this->assertEquals(
            [new PropertyPass('title', Direction::ASC, 0)],
            $result,
        );
    }

    public function testParseThrowsOnInvalidDirectionString(): void
    {
        $this->expectException(SortException::class);

        $this->propertyPassParser->parse(['title' => 'invalid']);
    }

    public function testParseThrowsOnNonStringDirectionInSpecs(): void
    {
        $this->expectException(SortException::class);

        // @phpstan-ignore argument.type
        $this->propertyPassParser->parse(['title' => ['direction' => 42]]);
    }

    public function testParseThrowsOnInvalidDirectionInSpecs(): void
    {
        $this->expectException(SortException::class);

        // @phpstan-ignore argument.type
        $this->propertyPassParser->parse(['title' => ['direction' => 'invalid']]);
    }

    public function testParseThrowsOnNonArrayFlags(): void
    {
        $this->expectException(SortException::class);

        // @phpstan-ignore argument.type
        $this->propertyPassParser->parse(['title' => ['flags' => 'not-an-array']]);
    }

    public function testParseThrowsOnNonIntegerFlag(): void
    {
        $this->expectException(SortException::class);

        // @phpstan-ignore argument.type
        $this->propertyPassParser->parse(['title' => ['flags' => ['not-an-int']]]);
    }

    public function testParseThrowsOnNumericIndexWithNonStringValue(): void
    {
        $this->expectException(SortException::class);

        // @phpstan-ignore argument.type
        $this->propertyPassParser->parse([['nested' => 'array']]);
    }
}