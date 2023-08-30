<?php

declare(strict_types=1);

namespace KennedyTedesco\Meilisearch\SearchFilter\Filters;

use KennedyTedesco\Meilisearch\SearchFilter\Operators\LogicalOperator;
use KennedyTedesco\Meilisearch\SearchFilter\SearchFilter;

final class GroupFilter implements Filter
{
    public function __construct(
        private readonly SearchFilter $searchFilter,
        private readonly LogicalOperator $logicalOperator,
    ) {
    }

    public function toString(): string
    {
        return "({$this->searchFilter->build()})";
    }

    public function logicalOperator(): LogicalOperator
    {
        return $this->logicalOperator;
    }
}
