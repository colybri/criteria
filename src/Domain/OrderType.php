<?php

declare(strict_types=1);

namespace Colybri\Criteria\Domain;

enum OrderType: string
{
    case Asc = 'asc';
    case Desc = 'desc';
    case None = 'none';
}
