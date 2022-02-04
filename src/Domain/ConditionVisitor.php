<?php

declare(strict_types=1);

namespace Colybri\Criteria\Domain;

interface ConditionVisitor
{
    public function visitAnd(Conjunction $filter);

    public function visitOr(Disjunction $filter);

    public function visitFilter(Filter $filter);
}