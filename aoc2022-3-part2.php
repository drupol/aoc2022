<?php

declare(strict_types=1);

namespace App;

use loophp\collection\Collection;

include __DIR__ . '/vendor/autoload.php';

$input = trim(file_get_contents(__DIR__ . '/input.txt'));

$priorities = array_flip(['null', ...range('a', 'z'), ...range('A', 'Z')]);

$c = Collection::fromString($input, "\n")
    ->map(static fn (string $line): array => str_split($line))
    ->chunk(3)
    ->map(
        static function (array $collections): string {
            $twoFirst = Collection::fromIterable($collections[0])
                ->filter(
                    static fn (string $letter): bool => Collection::fromIterable($collections[1])->contains($letter),
                );

            $twoLast = Collection::fromIterable($collections[1])
                ->filter(
                    static fn (string $letter): bool => Collection::fromIterable($collections[2])->contains($letter),
                );

            return $twoFirst->intersect(...$twoLast)->head();
        }
    )
    ->map(
        static fn (string $letter): int => $priorities[$letter]
    )
    ->reduce(
        static fn (int $c, int $i): int => $c + $i,
        0
    );

dump($c);
