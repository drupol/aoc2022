<?php

declare(strict_types=1);

namespace App;

use Generator;
use loophp\collection\Collection;
use SplStack;

include __DIR__ . '/vendor/autoload.php';

$input = trim(file_get_contents(__DIR__ . '/input.txt'));

$size = 4;

$c = Collection::fromString($input)
    ->window($size - 1)
    ->until(
        static fn (array $item): bool => $item === array_flip(array_flip($item))
    )
    ->flip();

print_r($c->last() + 1);
