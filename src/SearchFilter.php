<?php

declare(strict_types=1);

namespace KennedyTedesco\Meilisearch\SearchFilter;

use BadMethodCallException;
use Closure;
use InvalidArgumentException;
use KennedyTedesco\Meilisearch\SearchFilter\Filters\BetweenFilter;
use KennedyTedesco\Meilisearch\SearchFilter\Filters\ComparisonFilter;
use KennedyTedesco\Meilisearch\SearchFilter\Filters\Filter;
use KennedyTedesco\Meilisearch\SearchFilter\Filters\GroupFilter;
use KennedyTedesco\Meilisearch\SearchFilter\Filters\InFilter;
use KennedyTedesco\Meilisearch\SearchFilter\Filters\PresenceFilter;
use KennedyTedesco\Meilisearch\SearchFilter\Operators\ComparisonOperator;
use KennedyTedesco\Meilisearch\SearchFilter\Operators\LogicalOperator;
use KennedyTedesco\Meilisearch\SearchFilter\Operators\PrefixOperator;
use KennedyTedesco\Meilisearch\SearchFilter\Operators\PresenceOperator;
use KennedyTedesco\Meilisearch\SearchFilter\Support\StringBuilder;
use Stringable;

use function count;
use function in_array;
use function is_string;

/**
 * @method self where(...$args)
 * @method self whereGreaterThan(...$args)
 * @method self whereGt(...$args)
 * @method self whereGreaterThanOrEqual(...$args)
 * @method self whereGte(...$args)
 * @method self whereLessThan(...$args)
 * @method self whereLt(...$args)
 * @method self whereLessThanOrEqual(...$args)
 * @method self whereLte(...$args)
 * @method self whereNot(...$args)
 * @method self whereIn(...$args)
 * @method self whereNotIn(...$args)
 * @method self whereExists(...$args)
 * @method self whereNotExists(...$args)
 * @method self orWhere(...$args)
 * @method self orWhereGreaterThan(...$args)
 * @method self orWhereGt(...$args)
 * @method self orWhereGreaterThanOrEqual(...$args)
 * @method self orWhereGte(...$args)
 * @method self orWhereLessThan(...$args)
 * @method self orWhereLt(...$args)
 * @method self orWhereLessThanOrEqual(...$args)
 * @method self orWhereLte(...$args)
 * @method self orWhereNot(...$args)
 * @method self orWhereIn(...$args)
 * @method self orWhereNotIn(...$args)
 * @method self orWhereExists(...$args)
 * @method self orWhereNotExists(...$args)
 * @method self whereBetween(...$args)
 * @method self whereNotBetween(...$args)
 * @method self orWhereBetween(...$args)
 * @method self orWhereNotBetween(...$args)
 * @method self whereEmpty(...$args)
 * @method self whereNotEmpty(...$args)
 * @method self orWhereEmpty(...$args)
 * @method self orWhereNotEmpty(...$args)
 * @method self whereNull(...$args)
 * @method self whereNotNull(...$args)
 * @method self orWhereNull(...$args)
 * @method self orWhereNotNull(...$args)
 */
final class SearchFilter implements Stringable
{
    /** @var Filter[] */
    private array $filters = [];

    public static function new(): self
    {
        return new self();
    }

    /**
     * @param array<int, mixed> $args
     *
     * @throws BadMethodCallException
     * @throws InvalidArgumentException
     */
    public function __call(string $method, array $args = []): self
    {
        if (!str_starts_with($method, 'where') && !str_starts_with($method, 'orWhere')) {
            throw new BadMethodCallException("`{$method}()` is not a valid filter method");
        }

        if ($args === []) {
            throw new InvalidArgumentException('Missing arguments for filter');
        }

        $logicalOperator = str_starts_with($method, 'or') ? LogicalOperator::OR : LogicalOperator::AND;

        /**
         * @var Closure|string $attribute
         */
        $attribute = $args[0];

        // Closure is used to create a group filter
        if ($attribute instanceof Closure) {
            if (in_array($method, ['where', 'orWhere'], true)) {
                $attribute($searchFilter = self::new());

                return $this->append(new GroupFilter($searchFilter, $logicalOperator));
            }

            throw new BadMethodCallException('Only `where()` and `orWhere()` methods can receive a closure as argument.');
        }

        $prefixOperator = str_contains($method, 'Not') ? PrefixOperator::NOT : PrefixOperator::NONE;

        if (str_ends_with($method, 'In')) {
            return $this->append(new InFilter(
                attribute: $attribute,
                prefixOperator: $prefixOperator,
                values: $args[1] ?? null,
                logicalOperator: $logicalOperator,
            ));
        }

        if (str_ends_with($method, 'Exists')) {
            return $this->append(new PresenceFilter(
                attribute: $attribute,
                prefixOperator: $prefixOperator,
                presenceOperator: PresenceOperator::EXISTS,
                logicalOperator: $logicalOperator,
            ));
        }

        if (str_ends_with($method, 'Empty')) {
            return $this->append(new PresenceFilter(
                attribute: $attribute,
                prefixOperator: $prefixOperator,
                presenceOperator: PresenceOperator::EMPTY,
                logicalOperator: $logicalOperator,
            ));
        }

        if (str_ends_with($method, 'Null')) {
            return $this->append(new PresenceFilter(
                attribute: $attribute,
                prefixOperator: $prefixOperator,
                presenceOperator: PresenceOperator::NULL,
                logicalOperator: $logicalOperator,
            ));
        }

        if (str_ends_with($method, 'Between') && count($args) === 3) {
            return $this->append(new BetweenFilter(
                prefixOperator: $prefixOperator,
                attribute: $attribute,
                from: $args[1],
                to: $args[2],
                logicalOperator: $logicalOperator,
            ));
        }

        $value = $args[1] ?? null;

        if ($value === null) {
            throw new InvalidArgumentException('Missing value for filter. If you want to check for null values, use `whereNull()` or `whereNotNull()`');
        }

        $comparisonOperator = ComparisonOperator::fromMethodName($method);

        // If the method is called with 3 arguments, the second argument should be the comparison operator.
        // Example: where('score', '>=', 75)
        if (count($args) === 3) {
            $value = $args[2];

            /** @var ComparisonOperator|string $comparison */
            $comparison = $args[1] ?? ComparisonOperator::EQUALS;

            if (is_string($comparison)) {
                $comparisonOperator = ComparisonOperator::from($comparison);
            }
        }

        return $this->append(new ComparisonFilter(
            attribute: $attribute,
            comparisonOperator: $comparisonOperator,
            value: $value,
            logicalOperator: $logicalOperator,
        ));
    }

    public function when(bool $condition, Closure $callback): self
    {
        if ($condition) {
            $callback($this);
        }

        return $this;
    }

    public function build(): string
    {
        $output = StringBuilder::new();
        $lastIndex = count($this->filters) - 1;

        foreach ($this->filters as $currentIndex => $filter) {
            $stringBuilder = StringBuilder::new($filter->toString());

            $nextFilter = $this->filters[$currentIndex + 1] ?? null;

            if ($nextFilter instanceof Filter) {
                $stringBuilder->append(" {$nextFilter->logicalOperator()->value}");
            } elseif ($currentIndex < $lastIndex) {
                $stringBuilder->append(" {$filter->logicalOperator()->value}");
            }

            $output
                ->when($currentIndex > 0, function (StringBuilder $stringBuilder): void {
                    $stringBuilder->append(' ');
                })
                ->append($stringBuilder->toString());
        }

        return $output->toString();
    }

    private function append(Filter $filter): self
    {
        $this->filters[] = $filter;

        return $this;
    }

    public function __toString(): string
    {
        return $this->build();
    }
}
