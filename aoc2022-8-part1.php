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

$getTreesAround = static function (array $coords, array $matrix): array {
    [$x, $y] = $coords;

    $north = $south = $east = $west = [];

    for ($i = $y+1; $i < count($matrix); $i++) {
        $south[] = $matrix[$i][$x];
    }
    for ($i = $y-1; $i >= 0; $i--) {
        $north[] = $matrix[$i][$x];
    }
    for ($i = $x+1; $i < count($matrix); $i++) {
        $east[] = $matrix[$y][$i];
    }
    for ($i = $x-1; $i >= 0; $i--) {
        $west[] = $matrix[$y][$i];
    }

    return [
        's' => $south,
        'n' => $north,
        'e' => $east,
        'w' => $west,
    ];
};

$visibleTreesOnEdge = 0;
for ($y = 0; $y < count($matrix); $y++) {
    for ($x = 0; $x < count($matrix); $x++) {
        $treesAround = $getTreesAround([$x, $y], $matrix);

        $treesAround = array_map(
            static fn (array $data): array => array_merge([0], $data),
            $treesAround
        );

        $visible = 0;

        if ($matrix[$y][$x] > max($treesAround['n'])) {
            $visible = 1;
        }
        if ($matrix[$y][$x] > max($treesAround['s'])) {
            $visible = 1;
        }
        if ($matrix[$y][$x] > max($treesAround['w'])) {
            $visible = 1;
        }
        if ($matrix[$y][$x] > max($treesAround['e'])) {
            $visible = 1;
        }
        if ($x === 0 || $x === count($matrix) - 1) {
            $visible = 1;
        }
        if ($y === 0 || $y === count($matrix) - 1) {
            $visible = 1;
        }

        $visibleTreesOnEdge += $visible;
    }
}

dump($visibleTreesOnEdge);
