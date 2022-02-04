<?php

declare(strict_types=1);

namespace Colybri\Criteria\Domain;

final class Order
{
    private function __construct(private OrderBy $orderBy,  private OrderType $orderType)
    {
    }

    public static function from(OrderBy $orderBy,  OrderType $orderType)
    {
        return new self($orderBy, $orderType);
    }

    public static function none(): self
    {
        return new self(OrderBy::from(''), OrderType::None);
    }

    public function isNone(): bool
    {
        return $this->orderType() === OrderType::None;
    }

    public function orderBy(): OrderBy
    {
        return $this->orderBy;
    }

    public function orderType(): OrderType
    {
        return $this->orderType;
    }

    public function serialize(): string
    {
        return sprintf('%s.%s', $this->orderBy->value(), $this->orderType()->value);
    }
}