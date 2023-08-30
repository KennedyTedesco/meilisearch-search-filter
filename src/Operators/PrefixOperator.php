<?php

declare(strict_types=1);

namespace KennedyTedesco\Meilisearch\SearchFilter\Operators;

enum PrefixOperator: string
{
    case NOT = 'NOT';

    case NONE = '';
}
