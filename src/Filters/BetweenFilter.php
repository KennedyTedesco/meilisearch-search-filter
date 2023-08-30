<?php

declare(strict_types=1);

namespace KennedyTedesco\Meilisearch\SearchFilter\Filters;

use InvalidArgumentException;
use KennedyTedesco\Meilisearch\SearchFilter\Operators\LogicalOperator;
use KennedyTedesco\Meilisearch\SearchFilter\Operators\PrefixOperator;
use KennedyTedesco\Meilisearch\SearchFilter\Support\StringBuilder;

final class BetweenFilter implements Filter
{
    public function __construct(
        private readonly PrefixOperator $prefixOperator,
        private readonly string $attribute,
        private readonly mixed $from,
        private readonly mixed $to,
        private readonly LogicalOperator $logicalOperator,
    ) {
    }

    public function toString(): string
    {
        if (!is_numeric($this->from) || !is_numeric($this->to)) {
            throw new InvalidArgumentException('Invalid value type for this operation');
        }

        return StringBuilder::new()
            ->when($this->prefixOperator !== PrefixOperator::NONE, function (StringBuilder $stringBuilder): void {
                $stringBuilder->append("{$this->prefixOperator->value} ");
            })
            ->append("{$this->attribute} {$this->from} TO {$this->to}")
            ->toString();
    }

    public function logicalOperator(): LogicalOperator
    {
        return $this->logicalOperator;
    }
}
