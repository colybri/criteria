<?php

declare(strict_types=1);

namespace Colybri\Criteria\Tests\Infrastructure\Adapter\Dbal;

use Colybri\Criteria\Domain\Criteria;
use Colybri\Criteria\Domain\Disjunction;
use Colybri\Criteria\Domain\Filter;
use Colybri\Criteria\Domain\FilterField;
use Colybri\Criteria\Domain\FilterOperator;
use Colybri\Criteria\Domain\Filters;
use Colybri\Criteria\Domain\FilterValue;
use Colybri\Criteria\Domain\Order;
use Colybri\Criteria\Domain\OrderBy;
use Colybri\Criteria\Domain\OrderType;
use Colybri\Criteria\Infrastructure\Adapter\Dbal\CriteriaDbalAdapter;
use Colybri\Criteria\Infrastructure\Adapter\EntityMap;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\ParameterType;
use PHPUnit\Framework\TestCase;

final class CriteriaDbalAdapterTest extends TestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $this->connection->executeStatement('CREATE TABLE books (id INTEGER, title TEXT, author TEXT)');
        $this->connection->executeStatement(
            "INSERT INTO books VALUES (1, 'Dune', 'Herbert'), (2, 'Emma', 'Austen'), (3, 'Persuasion', 'Austen')"
        );
    }

    public function testItBuildsAndExecutesFilteredOrderedQuery(): void
    {
        $criteria = new Criteria(
            Filters::from(
                Filter::from(FilterField::from('writer'), FilterOperator::Equal, FilterValue::from('Austen')),
                Disjunction::fromFilters(
                    Filter::from(FilterField::from('name'), FilterOperator::Contains, FilterValue::from('PERS')),
                    Filter::from(FilterField::from('name'), FilterOperator::Equal, FilterValue::from('Emma')),
                ),
            ),
            Order::from(OrderBy::from('name'), OrderType::Desc),
            0,
            10,
        );

        $qb = $this->connection->createQueryBuilder()->select('id')->from('books');
        (new CriteriaDbalAdapter($qb, new BooksMap()))->build($criteria);

        self::assertSame(ParameterType::STRING, $qb->getParameterType('author1'));
        self::assertSame(['3', '2'], array_map('strval', $qb->executeQuery()->fetchFirstColumn()));
    }

    public function testInFiltersAreBoundAsStringArrays(): void
    {
        $criteria = new Criteria(
            Filters::from(Filter::from(FilterField::from('writer'), FilterOperator::In, FilterValue::from('Austen'))),
            Order::none(),
            null,
            null,
        );

        $qb = $this->connection->createQueryBuilder()->select('id')->from('books');
        (new CriteriaDbalAdapter($qb, new BooksMap()))->build($criteria);

        self::assertSame(ArrayParameterType::STRING, $qb->getParameterType('author1'));
        self::assertStringContainsString('author IN (:author1)', $qb->getSQL());
    }
}

final class BooksMap implements EntityMap
{
    public function map(string $attribute): string
    {
        return ['writer' => 'author', 'name' => 'title'][$attribute];
    }

    public static function table(): string
    {
        return 'books';
    }
}
