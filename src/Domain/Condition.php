<?php

declare(strict_types=1);

namespace Colybri\Criteria\Domain;

interface Condition
{
    public function accept(ConditionVisitor $visitor);
}