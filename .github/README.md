<div align="center">

# Meilisearch Search Filter

[![Build Status](https://github.com/KennedyTedesco/meilisearch-search-filter/actions/workflows/tests.yml/badge.svg)](https://img.shields.io/github/actions/workflow/status/KennedyTedesco/meilisearch-search-filter/tests.yml?label=tests)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg?style=flat)](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg?style=flat)
[![License](https://poser.pugx.org/kennedytedesco/meilisearch-search-filter/license)](//packagist.org/packages/kennedytedesco/meilisearch-search-filter)

</div>

This zero-dependency library provides a fluent and intuitive way to construct filters for Meilisearch queries. It
simplifies the process of building filters by offering a chainable API.

## Installation

**Minimum requirements:** PHP 8.1 or higher.

To use this library in your project, you can install it using Composer:

```bash
composer require kennedytedesco/meilisearch-search-filter "^1.0"
```

## Usage

You can learn how Meilisearch filters work by reading
the [official documentation](https://www.meilisearch.com/docs/learn/fine_tuning_results/filtering).

You should also check out the [Meilisearch PHP SDK](https://github.com/meilisearch/meilisearch-php).

Here are some examples that demonstrate how to build filters using this library:

```php
use Meilisearch\Client;
use KennedyTedesco\Meilisearch\SearchFilter\SearchFilter;

$client = new Client('http://127.0.0.1:7700', 'masterKey');

$filter = SearchFilter::new()
    ->where(function (SearchFilter $filter) {
        $filter->whereGreaterThan('rating.critics', 80)
            ->whereGreaterThanOrEqual('rating.users', 70);
    })
    ->whereIn('genres', ['Horror', 'Thriller']);

$index = $client->index('movies');

$results = $index->search('wonder', [
    // (rating.critics > 80 AND rating.users >= 70) AND genres IN ["Horror", "Thriller"]
    'filter' => $filter->build(),
]);
```

You can also use alias methods when constructing filters:

```php
use KennedyTedesco\Meilisearch\SearchFilter\SearchFilter;

$filter = SearchFilter::new()
    ->where(function (SearchFilter $filter) {
        $filter->whereGt('rating.critics', 80)
            ->whereGte('rating.users', 70);
    })
    ->orWhereIn('genres', ['Horror', 'Thriller']);

echo $filter->build(); 

// Output: (rating.critics > 80 AND rating.users >= 70) OR genres IN ["Horror", "Thriller"]
```

Alternatively, you can use the `where(...)` method and pass the operator as the second argument:

```php
use KennedyTedesco\Meilisearch\SearchFilter\SearchFilter;

$filter = SearchFilter::new()
    ->where(function (SearchFilter $filter) {
        $filter->where('rating.critics', '>', 80)
            ->where('rating.users', '>=', 70);
    })
    ->orWhereIn('genres', ['Horror', 'Thriller']);

echo $filter->build(); 

// Output: (rating.critics > 80 AND rating.users >= 70) OR genres IN ["Horror", "Thriller"]
```

#### Using Between

The `TO` operator is equivalent to `>=` AND `<=`.

For more details, see [this link](https://www.meilisearch.com/docs/learn/fine_tuning_results/filtering#to).

```php
use KennedyTedesco\Meilisearch\SearchFilter\SearchFilter;

$filter = SearchFilter::new()
    ->whereBetween('rating.critics', 80, 90);

echo $filter->build(); 

// Output: rating.critics 80 TO 90
```

If you want your results to only include "comedy" and "horror" movies released after March 1995, it's mandatory to group
the `OR` conditions:

```php
use KennedyTedesco\Meilisearch\SearchFilter\SearchFilter;

$filter = SearchFilter::new()
    ->where(function (SearchFilter $filter) {
        $filter->where('genres', 'horror')
            ->orWhere('genres', 'comedy');
    })
    ->where('release_date', '>', '795484800');

echo $filter->build();

// Output: (genres = "horror" OR genres = "comedy") AND release_date > 795484800
```

So, when you provide a closure to the `where()` or `orWhere()` methods, a fresh `SearchFilter` instance is passed to the
closure as the first argument. This lets you craft nested filters within parentheses.

#### Using `when()`

The `when()` method allows you to conditionally add filters to the query. For example:

```php
use KennedyTedesco\Meilisearch\SearchFilter\SearchFilter;

$filter = SearchFilter::new()
    ->when($request->filled('type'), function (SearchFilter $filter) use($request) {
        $filter->where('type', $request->get('type'));
    })
    ->where('release_date', '>', '795484800');
```

### All available filter methods

| Method                                | Description                                                                                                  |
|---------------------------------------|--------------------------------------------------------------------------------------------------------------|
| `where(...$args)`                     | Adds a filter condition using the `AND` logical operator.                                                    |
| `whereGreaterThan(...$args)`          | Adds a greater than comparison filter condition.                                                             |
| `whereGt(...$args)`                   | Alias for `whereGreaterThan`.                                                                                |
| `whereGreaterThanOrEqual(...$args)`   | Adds a greater than or equal to comparison filter condition.                                                 |
| `whereGte(...$args)`                  | Alias for `whereGreaterThanOrEqual`.                                                                         |
| `whereLessThan(...$args)`             | Adds a less than comparison filter condition.                                                                |
| `whereLt(...$args)`                   | Alias for `whereLessThan`.                                                                                   |
| `whereLessThanOrEqual(...$args)`      | Adds a less than or equal to comparison filter condition.                                                    |
| `whereLte(...$args)`                  | Alias for `whereLessThanOrEqual`.                                                                            |
| `whereNot(...$args)`                  | Adds a not equal comparison filter condition.                                                                |
| `whereIn(...$args)`                   | Adds a filter to check if the attribute value is in a given array of values.                                 |
| `whereNotIn(...$args)`                | Adds a filter to check if the attribute value is not in a given array of values.                             |
| `whereExists(...$args)`               | Adds a filter to check if the attribute value exists.                                                        |
| `whereNotExists(...$args)`            | Adds a filter to check if the attribute value does not exist.                                                |
| `orWhere(...$args)`                   | Adds a filter condition using the `OR` logical operator.                                                     |
| `orWhereGreaterThan(...$args)`        | Adds a greater than comparison filter condition using `OR` logical operator.                                 |
| `orWhereGt(...$args)`                 | Alias for `orWhereGreaterThan`.                                                                              |
| `orWhereGreaterThanOrEqual(...$args)` | Adds a greater than or equal to comparison filter condition using `OR` logical operator.                     |
| `orWhereGte(...$args)`                | Alias for `orWhereGreaterThanOrEqual`.                                                                       |
| `orWhereLessThan(...$args)`           | Adds a less than comparison filter condition using `OR` logical operator.                                    |
| `orWhereLt(...$args)`                 | Alias for `orWhereLessThan`.                                                                                 |
| `orWhereLessThanOrEqual(...$args)`    | Adds a less than or equal to comparison filter condition using `OR` logical operator.                        |
| `orWhereLte(...$args)`                | Alias for `orWhereLessThanOrEqual`.                                                                          |
| `orWhereNot(...$args)`                | Adds a not equal comparison filter condition using `OR` logical operator.                                    |
| `orWhereIn(...$args)`                 | Adds a filter to check if the attribute value is in a given array of values using `OR` logical operator.     |
| `orWhereNotIn(...$args)`              | Adds a filter to check if the attribute value is not in a given array of values using `OR` logical operator. |
| `orWhereExists(...$args)`             | Adds a filter to check if the attribute value exists using `OR` logical operator.                            |
| `orWhereNotExists(...$args)`          | Adds a filter to check if the attribute value does not exist using `OR` logical operator.                    |
| `whereBetween(...$args)`              | Adds a filter to check if the attribute value is between two values.                                         |
| `whereNotBetween(...$args)`           | Adds a filter to check if the attribute value is not between two values.                                     |
| `orWhereBetween(...$args)`            | Adds a filter to check if the attribute value is between two values using `OR` logical operator.             |
| `orWhereNotBetween(...$args)`         | Adds a filter to check if the attribute value is not between two values using `OR` logical operator.         |
| `whereEmpty(...$args)`                | Adds a filter to check if the attribute value is empty.                                                      |
| `whereNotEmpty(...$args)`             | Adds a filter to check if the attribute value is not empty.                                                  |
| `orWhereEmpty(...$args)`              | Adds a filter to check if the attribute value is empty using `OR` logical operator.                          |
| `orWhereNotEmpty(...$args)`           | Adds a filter to check if the attribute value is not empty using `OR` logical operator.                      |
| `whereNull(...$args)`                 | Adds a filter to check if the attribute value is null.                                                       |
| `whereNotNull(...$args)`              | Adds a filter to check if the attribute value is not null.                                                   |
| `orWhereNull(...$args)`               | Adds a filter to check if the attribute value is null using `OR` logical operator.                           |
| `orWhereNotNull(...$args)`            | Adds a filter to check if the attribute value is not null using `OR` logical operator.                       |

## Contributing

If you'd like to contribute to this project, feel free to submit pull requests or open issues on the GitHub repository.
