<?php

declare(strict_types=1);

namespace KennedyTedesco\Meilisearch\SearchFilter\Operators;

enum PresenceOperator: string
{
    case EXISTS = 'EXISTS';
    case NULL = 'IS NULL';

    case EMPTY = 'IS EMPTY';
}
