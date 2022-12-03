<?php

declare(strict_types=1);

namespace App;

use loophp\collection\Collection;

include __DIR__ . '/vendor/autoload.php';

$input = trim(file_get_contents(__DIR__ . '/input.txt'));

$priorities = array_flip(['null', ...range('a', 'z'), ...range('A', 'Z')]);

$c = Collection::fromString($input, "\n")
    ->associate(
        static fn (int $key, string $line): int => strlen($line),
        static fn (string $line): Collection => Collection::fromString($line)
    )
    ->map(
        static function (Collection $collection, int $size): string {
            $data = $collection->span(
                static fn (string $letter, int $index): bool => $index < $size/2
            )->all();

            return $data[0]->intersect(...$data[1])->head();
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
