<?php

declare(strict_types=1);

namespace KennedyTedesco\Meilisearch\SearchFilter\Filters;

use KennedyTedesco\Meilisearch\SearchFilter\Operators\LogicalOperator;

interface Filter
{
    public function toString(): string;

    public function logicalOperator(): LogicalOperator;
}
