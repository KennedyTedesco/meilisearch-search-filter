<?php

declare(strict_types=1);

namespace Tests;

use BadMethodCallException;
use InvalidArgumentException;
use KennedyTedesco\Meilisearch\SearchFilter\SearchFilter;
use ValueError;

test('test comparison filters', function () {
    $query = SearchFilter::new()
        ->where('release_date', 1077884550);

    expect($query->build())
        ->toBe('release_date = 1077884550');

    $query = SearchFilter::new()
        ->where(function (SearchFilter $filter) {
            $filter->where('release_date', 1077884550);
        });

    expect($query->build())
        ->toBe('(release_date = 1077884550)');

    $query = SearchFilter::new()
        ->whereGreaterThan('release_date', 1077884550)
        ->orWhereLessThan('release_date', 1577884550);

    expect($query->build())
        ->toBe('release_date > 1077884550 OR release_date < 1577884550');

    $query = SearchFilter::new()
        ->when(true, function (SearchFilter $filter) {
            $filter->whereGreaterThan('release_date', 1077884550)
                ->whereLessThan('release_date', 1577884550);
        })
        ->orWhere(function (SearchFilter $filter) {
            $filter->where('director', 'Tim Burton')
                ->where('director_type', 1);
        });

    expect($query->build())
        ->toBe('release_date > 1077884550 AND release_date < 1577884550 OR (director = "Tim Burton" AND director_type = 1)');

    $query = SearchFilter::new()
        ->where('release_date', '>', 1077884550)
        ->where('release_date', '<', 1577884550)
        ->orWhere(function (SearchFilter $filter) {
            $filter->where('director', 'Tim Burton')
                ->where('director_type', 1);
        });

    expect($query->build())
        ->toBe('release_date > 1077884550 AND release_date < 1577884550 OR (director = "Tim Burton" AND director_type = 1)');

    $query = SearchFilter::new()
        ->where(function (SearchFilter $filter) {
            $filter->whereGreaterThan('release_date', 1077884550)
                ->whereLessThan('release_date', 1577884550);
        })
        ->orWhere(function (SearchFilter $filter) {
            $filter->where('director', 'Tim Burton')
                ->where('director_type', 1);
        });

    expect($query->build())
        ->toBe('(release_date > 1077884550 AND release_date < 1577884550) OR (director = "Tim Burton" AND director_type = 1)');

    $query = SearchFilter::new()
        ->where(function (SearchFilter $filter) {
            $filter->whereGreaterThan('release_date', 1077884550)
                ->where(function (SearchFilter $filter) {
                    $filter->where('director', 'Tim Burton')
                        ->where('director_type', 1);
                });
        });

    expect($query->build())
        ->toBe('(release_date > 1077884550 AND (director = "Tim Burton" AND director_type = 1))');

    $query = SearchFilter::new()
        ->where(function (SearchFilter $filter) {
            $filter->whereGreaterThan('release_date', 1077884550)
                ->orWhereLessThan('release_date', 1577884550);
        })
        ->orWhere(function (SearchFilter $filter) {
            $filter->where('director', 'Tim Burton')
                ->where('director_type', 1);
        });

    expect($query->build())
        ->toBe('(release_date > 1077884550 OR release_date < 1577884550) OR (director = "Tim Burton" AND director_type = 1)');

    $query = SearchFilter::new()
        ->where(function (SearchFilter $filter) {
            $filter->whereGreaterThan('release_date', 1077884550)
                ->orWhereLessThan('release_date', 1577884550);
        })
        ->where(function (SearchFilter $filter) {
            $filter->where('director', 'Tim Burton')
                ->where('director_type', 1);
        });

    expect($query->build())
        ->toBe('(release_date > 1077884550 OR release_date < 1577884550) AND (director = "Tim Burton" AND director_type = 1)');

    $query = SearchFilter::new()
        ->whereGt('release_date', 1077884550)
        ->whereGte('release_date', 1077884550)
        ->whereLt('release_date', 1077884550)
        ->whereLte('release_date', 1077884550);

    expect($query->build())
        ->toBe('release_date > 1077884550 AND release_date >= 1077884550 AND release_date < 1077884550 AND release_date <= 1077884550');

    $query = SearchFilter::new()
        ->whereGt('release_date', 1077884550)
        ->orWhereGte('release_date', 1077884550)
        ->orWhereLt('release_date', 1077884550)
        ->orWhereLte('release_date', 1077884550);

    expect($query->build())
        ->toBe('release_date > 1077884550 OR release_date >= 1077884550 OR release_date < 1077884550 OR release_date <= 1077884550');

    $query = SearchFilter::new()
        ->where(function (SearchFilter $filter) {
            $filter->where('genres', 'Horror')
                ->orWhere(function (SearchFilter $filter) {
                    $filter->where('rating.critics', '>', 80)
                        ->where('rating.users', '>=', 70);
                });
        })
        ->orWhere('director', 'Jordan Peele');

    expect($query->build())
        ->toBe('(genres = "Horror" OR (rating.critics > 80 AND rating.users >= 70)) OR director = "Jordan Peele"');

    $query = SearchFilter::new()
        ->where('genres', 'Thriller')
        ->orWhere(function (SearchFilter $filter) {
            $filter->whereBetween('rating.critics', 70, 90)
                ->where('director', 'Jordan Peele');
        })
        ->where('release_date', '>', 1609459200);

    expect($query->build())
        ->toBe('genres = "Thriller" OR (rating.critics 70 TO 90 AND director = "Jordan Peele") AND release_date > 1609459200');

    $query = SearchFilter::new()
        ->where(function (SearchFilter $filter) {
            $filter->where('rating.critics', '>', 80)
                ->where('rating.users', '>=', 70);
        })
        ->orWhere(function (SearchFilter $filter) {
            $filter->where('genres', 'Horror')
                ->where('director', 'Jordan Peele');
        });

    expect($query->build())
        ->toBe('(rating.critics > 80 AND rating.users >= 70) OR (genres = "Horror" AND director = "Jordan Peele")');
});

test('test whereIn filter', function () {
    $query = SearchFilter::new()
        ->where('type', 1)
        ->where(function (SearchFilter $filter) {
            $filter->whereIn('director', ['Tim Burton 1', 'Tim Burton 2'])
                ->whereNotIn('director', ['Tim Burton 3', 'Tim Burton 4']);
        });

    expect($query->build())
        ->toBe('type = 1 AND (director IN ["Tim Burton 1","Tim Burton 2"] AND NOT director IN ["Tim Burton 3","Tim Burton 4"])');

    $query = SearchFilter::new()
        ->where('type', 1)
        ->where(function (SearchFilter $filter) {
            $filter->whereIn('director', ['Tim Burton 1', 'Tim Burton 2'])
                ->orWhereNotIn('director', ['Tim Burton 3', 'Tim Burton 4']);
        });

    expect($query->build())
        ->toBe('type = 1 AND (director IN ["Tim Burton 1","Tim Burton 2"] OR NOT director IN ["Tim Burton 3","Tim Burton 4"])');

    $query = SearchFilter::new()
        ->where('type', 1)
        ->orWhere(function (SearchFilter $filter) {
            $filter->whereIn('director', ['Tim Burton 1', 'Tim Burton 2'])
                ->orWhereIn('director', ['Tim Burton 3', 'Tim Burton 4']);
        });

    expect($query->build())
        ->toBe('type = 1 OR (director IN ["Tim Burton 1","Tim Burton 2"] OR director IN ["Tim Burton 3","Tim Burton 4"])');
});

test('test between filter', function () {
    $query = SearchFilter::new()
        ->where(function (SearchFilter $filter) {
            $filter->whereBetween('user.rating', 80, 90)
                ->whereNotBetween('risk', 0.4, 1.0);
        });

    expect($query->build())
        ->toBe('(user.rating 80 TO 90 AND NOT risk 0.4 TO 1)');
});

test('test presence filter', function () {
    $query = SearchFilter::new()
        ->where('type', 1)
        ->where(function (SearchFilter $filter) {
            $filter->whereExists('bar')
                ->orWhereExists('baz');
        })
        ->whereNotExists('status')
        ->where(function (SearchFilter $filter) {
            $filter->whereExists('foo')
                ->orWhereNotExists('bar');
        });

    expect($query->build())
        ->toBe('type = 1 AND (bar EXISTS OR baz EXISTS) AND NOT status EXISTS AND (foo EXISTS OR NOT bar EXISTS)');

    $query = SearchFilter::new()
        ->whereNotEmpty('name')
        ->whereNull('fraud');

    expect($query->build())
        ->toBe('NOT name IS EMPTY AND fraud IS NULL');
});

test('throws exception for calling where with null value', function () {
    $this->expectException(InvalidArgumentException::class);

    SearchFilter::new()
        ->where('type', null)
        ->build();
});

test('throws exception for calling where without a value', function () {
    $this->expectException(InvalidArgumentException::class);

    SearchFilter::new()
        ->where('type')
        ->build();
});

test('throws exception for calling where without any argument', function () {
    $this->expectException(InvalidArgumentException::class);

    SearchFilter::new()
        ->where()
        ->build();
});

test('throws exception for calling with an invalid method name', function () {
    $this->expectException(BadMethodCallException::class);

    SearchFilter::new()
        ->andWhere()
        ->build();
});

test('throws exception for invalid closure in whereNull', function () {
    $this->expectException(BadMethodCallException::class);

    SearchFilter::new()
        ->whereNull(function (SearchFilter $filter) {
            $filter->where('type', 1);
        })
        ->build();
});

test('throws exception for invalid comparison operator in where', function () {
    $this->expectException(ValueError::class);

    SearchFilter::new()
        ->where('type', '<>', 1);
});

test('throws exception for invalid associative array in whereIn', function () {
    $this->expectException(InvalidArgumentException::class);

    SearchFilter::new()
        ->whereIn('type', ['type_1' => 1, 'type_2' => 2])
        ->build();
});
