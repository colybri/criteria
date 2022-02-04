<h1 align="center">Criteria</h1>

This package allows build customized criteria and any kind of filters and also include an adapter for use with Doctrine Database Abstraction Layer.

## Domain Driven Design series
This package is the first of a series of high level building blocks to build your applications with Domain Driven Design  approach.

- <a href="#">Criteria</a>


## Table of contents

- <a href="#installation">Installation</a>
- <a href="#usage">Usage</a>
    - <a href="#create-a-criteria">Create a criteria</a>
    - <a href="#specify-order">Specify order</a>
    - <a href="#limit-results">Limit results</a>
    - <a href="#nested-filters">Nested filters</a>
- <a href="#adapter">Adapter</a>
    - <a href="#dbal-adapter">Dbal adapter</a>
    - <a href="#map-database-fields">Map database fields</a>

## Installation

Via Composer

``` bash
$ composer require colybri/criteria
```

## Usage

### Create a criteria

```php
new Criteria(
    Filters::from([
        Filter::from(
            FilterField::from(CountryName::class),
            FilterOperator::Contains,
            FilterValue::from('Samoa')
         ),
        Filter::from(
            FilterField::from(CountryAlpha2Code::class),
            FilterOperator::Contains,
            FilterValue::from('WS')
        )
        ...
    ]),
    Order::from(OrderBy::from(CountryName::class), OrderType::Desc),
    0,
    100
);   
```
A filter is composed by three objects. First is field key to match with your key/value map of field's names of your columns on database. Second parameter is the operator you want to use on your condition. The last one is the value you want to match.

### Specify order

First parameter is the key of the field for order result. Secondly, order type.
```php
Order::from(OrderBy::from(CountryName::class), OrderType::Desc),
```
### Limit results

If you don't want to limit your results simply set `null` to the two last parameters of `Criteria`. Otherwise set offset and limit.
### Nested filters

In order use logic operator `OR` or nested conditions.  
```php
new Criteria(
    Filters::from(
        Conjunction::fromfilters(   
            Disjunction::fromfilters(
                Filter::from(
                    FilterField::from(CountryAlpha2Code::class),
                    FilterOperator::Equal,
                    FilterValue::from('SW')
                 ),
                Filter::from(
                    FilterField::from(CountryAlpha2Code::class),
                    FilterOperator::Equal,
                    FilterValue::from('WS')
                )
                ...
            ),
            Filter::from(
                 FilterField::from(CountryName::class),
                 FilterOperator::Contains,
                 FilterValue::from('Samoa')
            ),
            ...
        )
    ),
    Order::none(),
    null,
    null
);
```


## Adapter
### Dbal adapter

In your repository:

```php
        $query = $this->dbalConnection->createQueryBuilder()
            ->select('*')->from('countries');

        (new CriteriaDbalAdapter($query, new CountryMap()))->build($criteria);

        $countries = $query->executeQuery()->fetchAllAssociative();
```

### Map database fields

In the above example `CountryMap` is a simple class that must implement `EntityMap` interface. As show bellow. The attribute `FIELDS` is a key/value array where the key is the semantic name that you use on your domain and the value is the column name that match on database.
```php
final class CountryMap implements EntityMap
{
    private const FIELDS = [
        CountryName::class => 'en_short_name',
        CountryAlpha2Code::class => 'alpha_2_code'
    ];

    private const TABLE = 'countries';

    public function map(string $attribute): string
    {
        return self::FIELDS[$attribute];
    }

    public static function table(): string
    {
        return self::TABLE;
    }
}
```

## Credits

- [Mario J. López](https://github.com/colybri)

## License

[MIT](http://opensource.org/licenses/MIT)
