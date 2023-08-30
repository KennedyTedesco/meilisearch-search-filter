<?php

declare(strict_types=1);

namespace KennedyTedesco\Meilisearch\SearchFilter\Filters;

use InvalidArgumentException;
use JsonException;
use KennedyTedesco\Meilisearch\SearchFilter\Operators\LogicalOperator;
use KennedyTedesco\Meilisearch\SearchFilter\Operators\PrefixOperator;
use KennedyTedesco\Meilisearch\SearchFilter\Support\StringBuilder;

use function is_array;

use const JSON_THROW_ON_ERROR;

final class InFilter implements Filter
{
    public function __construct(
        private readonly string $attribute,
        private readonly PrefixOperator $prefixOperator,
        private readonly mixed $values,
        private readonly LogicalOperator $logicalOperator,
    ) {
    }

    /**
     * @throws JsonException
     */
    public function toString(): string
    {
        $values = array_filter(is_array($this->values) ? $this->values : [$this->values]);

        if (!array_is_list($values)) {
            throw new InvalidArgumentException(
                'Instead of an associative array, you should provide a list of values. An example: ["value1", "value2"].',
            );
        }

        return StringBuilder::new()
            ->when($this->prefixOperator !== PrefixOperator::NONE, function (StringBuilder $stringBuilder): void {
                $stringBuilder->append("{$this->prefixOperator->value} ");
            })
            ->append("{$this->attribute} ")
            ->append("IN ")
            ->append(json_encode($values, JSON_THROW_ON_ERROR))
            ->toString();
    }

    public function logicalOperator(): LogicalOperator
    {
        return $this->logicalOperator;
    }
}
