<?php

declare(strict_types=1);

namespace KennedyTedesco\Meilisearch\SearchFilter\Filters;

use InvalidArgumentException;
use KennedyTedesco\Meilisearch\SearchFilter\Operators\ComparisonOperator;
use KennedyTedesco\Meilisearch\SearchFilter\Operators\LogicalOperator;

use function is_scalar;
use function is_string;

final class ComparisonFilter implements Filter
{
    public function __construct(
        private readonly string $attribute,
        private readonly ComparisonOperator $comparisonOperator,
        private readonly mixed $value,
        private readonly LogicalOperator $logicalOperator,
    ) {
    }

    public function toString(): string
    {
        if (!is_scalar($this->value)) {
            throw new InvalidArgumentException('Invalid value type for the comparison filter');
        }

        $value = $this->value;

        if (is_string($value)) {
            $value = sprintf('"%s"', $value);
        }

        return "{$this->attribute} {$this->comparisonOperator->value} {$value}";
    }

    public function logicalOperator(): LogicalOperator
    {
        return $this->logicalOperator;
    }
}
