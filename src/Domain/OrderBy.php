<?php

declare(strict_types=1);

namespace Colybri\Criteria\Domain;

final class OrderBy
{
    private function __construct(private string $value)
    {
    }

    public static function from($value): static
    {
        return new static($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}