<?php

declare(strict_types=1);

namespace KennedyTedesco\Meilisearch\SearchFilter\Operators;

enum LogicalOperator: string
{
    case AND = 'AND';
    case OR = 'OR';
}
