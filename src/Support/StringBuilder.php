<?php

declare(strict_types=1);

namespace KennedyTedesco\Meilisearch\SearchFilter\Support;

use Closure;

final class StringBuilder
{
    private function __construct(
        private string $string = '',
    ) {
    }

    public static function new(string $str = ''): self
    {
        return new self($str);
    }

    public function append(string $str): self
    {
        $this->string .= $str;

        return $this;
    }

    public function when(bool $condition, Closure $callback): self
    {
        if ($condition) {
            $callback($this);
        }

        return $this;
    }

    public function toString(): string
    {
        return $this->string;
    }
}
