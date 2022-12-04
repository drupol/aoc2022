<?php

declare(strict_types=1);

namespace App;

use Generator;
use loophp\collection\Collection;

include __DIR__ . '/vendor/autoload.php';

$input = trim(file_get_contents(__DIR__ . '/input.txt'));

$c = Collection::fromString($input, "\n")
    ->flatMap(
        static fn (string $line): Generator => yield from explode(",", $line)
    )
    ->flatMap(
        static fn (string $line): Generator => yield from explode("-", $line)
    )
    ->unwrap()
    ->chunk(2)
    ->chunk(2)
    ->filter(
        static function (array $data): bool {
            [$left, $right] = $data;

            if ($right[0] < $left[0]) {
                return false;
            }

            if ($right[1] > $left[1]) {
                return false;
            }

            return true;
        },
        static function (array $data): bool {
            [$right, $left] = $data;

            if ($right[0] < $left[0]) {
                return false;
            }

            if ($right[1] > $left[1]) {
                return false;
            }

            return true;
        },
    );

dump($c->count());
