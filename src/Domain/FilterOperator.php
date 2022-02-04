<?php

declare(strict_types=1);

namespace Colybri\Criteria\Domain;

enum FilterOperator: string
{
    case Equal = '=';
    case GreaterThan = '>';
    case GreaterThanOrEqual = '>=';
    case LessThan = '<';
    case LessThanOrEqual = '<=';
    case Contains = 'CONTAINS';
    case NotContains = 'NOT CONTAINS';
    case In = 'IN';
    case NotIn = 'NOT IN';
    case NotEqual = 'NOT EQUAL';
    case IsNull = 'IS NULL';
    case IsNotNull = 'IS NOT NULL';
}