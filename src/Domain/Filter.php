<?php

declare(strict_types=1);

namespace Colybri\Criteria\Domain;

final class Filter implements Condition
{
    private function __construct(
        private FilterField    $field,
        private FilterOperator $operator,
        private FilterValue    $value
    )
    {
    }

    public static function from(FilterField $field, FilterOperator $operator, FilterValue $value): self
    {
        return new self(
            $field,
            $operator,
            $value
        );
    }

    public function field(): FilterField
    {
        return $this->field;
    }

    public function operator(): FilterOperator
    {
        return $this->operator;
    }

    public function operatorValue(): string
    {
        return $this->operator()->value;
    }

    public function value(): FilterValue
    {
        return $this->value;
    }

    public function accept(ConditionVisitor $visitor)
    {
        return $visitor->visitFilter($this);
    }

    public function serialize(): string
    {
        return sprintf('%s.%s.%s', $this->field->value(), $this->operatorValue(), $this->value->value());
    }
}