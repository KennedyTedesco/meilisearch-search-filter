<?php

declare(strict_types=1);

namespace KennedyTedesco\Meilisearch\SearchFilter\Filters;

use KennedyTedesco\Meilisearch\SearchFilter\Operators\LogicalOperator;
use KennedyTedesco\Meilisearch\SearchFilter\Operators\PrefixOperator;
use KennedyTedesco\Meilisearch\SearchFilter\Operators\PresenceOperator;
use KennedyTedesco\Meilisearch\SearchFilter\Support\StringBuilder;

final class PresenceFilter implements Filter
{
    public function __construct(
        private readonly string $attribute,
        private readonly PrefixOperator $prefixOperator,
        private readonly PresenceOperator $presenceOperator,
        private readonly LogicalOperator $logicalOperator,
    ) {
    }

    public function toString(): string
    {
        return StringBuilder::new()
            ->when($this->prefixOperator !== PrefixOperator::NONE, function (StringBuilder $stringBuilder): void {
                $stringBuilder->append("{$this->prefixOperator->value} ");
            })
            ->append("{$this->attribute} ")
            ->append($this->presenceOperator->value)
            ->toString();
    }

    public function logicalOperator(): LogicalOperator
    {
        return $this->logicalOperator;
    }
}
