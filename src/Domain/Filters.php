<?php

declare(strict_types=1);

namespace Colybri\Criteria\Domain;

final class Filters implements \Countable, \IteratorAggregate
{
    private function __construct(private array $filters)
    {
    }

    public function count()
    {
        return count($this->filters());
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->filters());
    }

    public static function from(Condition ...$filters): static
    {
        return new static($filters);
    }

    public function serialize(): string
    {
        return array_reduce(
            static fn(string $accumulate, Filter $filter) => sprintf('%s^%s', $accumulate, $filter->serialize()),
            $this->filters(),
            ''
        );
    }

    protected function filters(): array
    {
        return $this->filters;
    }
}