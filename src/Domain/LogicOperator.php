<?php

declare(strict_types=1);

namespace Colybri\Criteria\Domain;

class LogicOperator
{
    private function __construct(private array $filters)
    {
    }

    public static function fromFilters(Condition ...$filters): static
    {
        return new static($filters);
    }

    public function filters()
    {
        return $this->filters;
    }
}