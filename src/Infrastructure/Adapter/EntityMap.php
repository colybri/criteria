<?php

declare(strict_types=1);

namespace Colybri\Criteria\Infrastructure\Adapter;

interface EntityMap
{
    public function map(string $attribute): string;

    public static function table(): string;
}