<?php

declare(strict_types=1);

namespace Colybri\Criteria\Infrastructure\Adapter\Dbal;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Connection;
use Colybri\Criteria\Domain\Conjunction;
use Colybri\Criteria\Domain\Disjunction;
use Colybri\Criteria\Domain\Filter;
use Colybri\Criteria\Domain\FilterOperator;
use Colybri\Criteria\Domain\Condition;
use Colybri\Criteria\Domain\Criteria;
use Colybri\Criteria\Domain\ConditionVisitor;
use Colybri\Criteria\Infrastructure\Adapter\EntityMap;

class CriteriaDbalAdapter implements ConditionVisitor
{
    private int $countParams;

    public function __construct(private QueryBuilder $queryBuilder, private EntityMap $entityMap)
    {
        $this->countParams = 0;
    }

    public function build(Criteria $criteria): void
    {

        foreach ($criteria->filters() as $theFilter) {
            $this->queryBuilder->andWhere($this->buildExpression($theFilter));
        }

        if ($criteria->hasOrder()) {
            $this->queryBuilder->orderBy(
                $this->mapFieldValue($criteria->order()->orderBy()->value()),
                $criteria->order()->orderTypeValue(),
            );
        }

        if (null !== $criteria->offset()) {
            $this->queryBuilder->setFirstResult($criteria->offset());
        }

        if (null !== $criteria->limit()) {
            $this->queryBuilder->setMaxResults($criteria->limit());
        }
    }

    public function visitAnd(Conjunction $statement): string
    {
        $expression ='( ';
        foreach ($statement->filters() as $index => $filter) {
            $expression .= $this->buildExpression($filter).' ';
            if ($index !== array_key_last($statement->filters())) {
                $expression .= 'AND ';
            }
        }
        $expression .= ' )';
        return $expression;
    }

    public function visitOr(Disjunction $statement): string
    {
        $expression ='( ';
        foreach ($statement->filters() as $index => $filter) {
            $expression .= $this->buildExpression($filter).' ';
            if ($index !== array_key_last($statement->filters())) {
                $expression .= 'OR ';
            }
        }
        $expression .= ' )';
        return $expression;
    }

    public function visitFilter(Filter $filter): string
    {
        $this->countParams++;

        $this->queryBuilder->setParameter(
            $this->mapFieldValue($filter->field()->value()) . $this->countParams,
            $this->mapParameter($filter),
            $this->mapType($filter),
        );

        return ($filter->operator() === FilterOperator::Contains
                ? 'LOWER('.$this->mapFieldValue($filter->field()->value()).')'
                : $this->mapFieldValue($filter->field()->value()) )
            . ' '
            . $this->mapOperator($filter)
            . (\in_array($filter->operator(), [FilterOperator::In, FilterOperator::NotIn]) ? ' (' : ' ')
            . ':'.$this->mapFieldValue($filter->field()->value()).$this->countParams
            . (\in_array($filter->operator(), [FilterOperator::In, FilterOperator::NotIn]) ? ')' : '');
    }

    private function buildExpression(Condition $filter)
    {
        return $filter->accept($this);
    }

    private function mapType(Filter $filter): ?int
    {
        if (\in_array($filter->operator(), [FilterOperator::In, FilterOperator::NotIn])) {
            return Connection::PARAM_STR_ARRAY;
        }

        return null;
    }

    private function mapOperator(Filter $filter): string
    {
        if (FilterOperator::Contains === $filter->operator()) {
            return 'LIKE';
        }
        if (FilterOperator::NotContains === $filter->operator()) {
            return 'NOT LIKE';
        }
        if (FilterOperator::NotEqual === $filter->operator()) {
            return '<>';
        }

        return $filter->operatorValue();
    }

    private function mapParameter(Filter $filter)
    {
        if (FilterOperator::Contains === $filter->operator() || FilterOperator::NotContains === $filter->operator()) {
            return '%' . strtolower($filter->value()->value()) . '%';
        }

        return $filter->value()->value();
    }

    private function mapFieldValue($value)
    {
        return  $this->entityMap->map($value);
    }
}