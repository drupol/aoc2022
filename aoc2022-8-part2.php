<?php

declare(strict_types=1);

namespace App;

use Exception;
use Generator;
use loophp\collection\Collection;
use loophp\collection\Contract\Operation\Splitable;
use SplStack;

include __DIR__ . '/vendor/autoload.php';

$input = trim(file_get_contents(__DIR__ . '/input.txt'));

$collection = Collection::fromFile(__DIR__ . '/input.txt')
    ->split(Splitable::REMOVE, static fn (string $c): bool => $c === "\n");

$matrix = $collection->all();

$scenicScore = static function (array $coords, array $matrix): int {
    [$x, $y] = $coords;
    $height = $matrix[$y][$x];

    $north = $south = $east = $west = 0;

    for ($i = $y+1; $i < count($matrix); $i++) {
        $south++;

        if ($matrix[$i][$x]>=$height) {
            break;
        }
    }
    for ($i = $y-1; $i >= 0; $i--) {
        $north++;
        if ($matrix[$i][$x]>=$height) {
            break;
        }
    }
    for ($i = $x+1; $i < count($matrix); $i++) {
        $east++;
        if ($matrix[$y][$i]>=$height) {
            break;
        }
    }
    for ($i = $x-1; $i >= 0; $i--) {
        $west++;
        if ($matrix[$y][$i]>=$height) {
            break;
        }
    }

    return $south * $north * $east * $west;
};

$list = [];
for ($y = 0; $y < count($matrix); $y++) {
    for ($x = 0; $x < count($matrix); $x++) {
        $list[] = $scenicScore([$x, $y], $matrix);
    }
}

asort($list);

dump(end($list));
