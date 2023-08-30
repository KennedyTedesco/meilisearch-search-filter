<?php

declare(strict_types=1);

namespace KennedyTedesco\Meilisearch\SearchFilter\Operators;

enum ComparisonOperator: string
{
    case EQUALS = '=';
    case NOT_EQUALS = '!=';
    case GREATER_THAN = '>';
    case GREATER_THAN_OR_EQUAL = '>=';
    case LESS_THAN = '<';
    case LESS_THAN_OR_EQUAL = '<=';

    public static function fromMethodName(string $name): self
    {
        return match (true) {
            str_ends_with($name, 'NotEquals') => self::NOT_EQUALS,
            str_ends_with($name, 'GreaterThan') => self::GREATER_THAN,
            str_ends_with($name, 'Gt') => self::GREATER_THAN,
            str_ends_with($name, 'GreaterThanOrEqual') => self::GREATER_THAN_OR_EQUAL,
            str_ends_with($name, 'Gte') => self::GREATER_THAN_OR_EQUAL,
            str_ends_with($name, 'LessThan') => self::LESS_THAN,
            str_ends_with($name, 'Lt') => self::LESS_THAN,
            str_ends_with($name, 'LessThanOrEqual') => self::LESS_THAN_OR_EQUAL,
            str_ends_with($name, 'Lte') => self::LESS_THAN_OR_EQUAL,
            default => self::EQUALS,
        };
    }
}
