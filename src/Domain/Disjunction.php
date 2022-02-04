<?php

declare(strict_types=1);

namespace Colybri\Criteria\Domain;

final class Disjunction extends LogicOperator implements Condition
{
    public function accept(ConditionVisitor $visitor)
    {
        return $visitor->visitOr($this);
    }
}