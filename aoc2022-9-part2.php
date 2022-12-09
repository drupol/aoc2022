<?php

declare(strict_types=1);

namespace App;

use Generator;
use loophp\collection\Collection;

include __DIR__ . '/vendor/autoload.php';

$addCoords = static function (array $coord1, array $coord2): array {
    return [$coord1[0] + $coord2[0], $coord1[1] + $coord2[1]];
};

$sign = static fn (int $a): int => $a === 0 ? 0 : $a / abs($a);

$move = static function (array $snake, array $coord) use ($addCoords, $sign): array {
    $snake[0] = $addCoords($snake[0], $coord);

    for ($i = 0; $i < count($snake) - 1; $i++) {
        $dx = $snake[$i][0] - $snake[$i+1][0];
        $dy = $snake[$i][1] - $snake[$i+1][1];

        if (abs($dx) > 1 || abs($dy) > 1) {
            $snake[$i+1][0] += $sign($dx);
            $snake[$i+1][1] += $sign($dy);
        }
    }

    return $snake;
};

$visited = [];

$snake = Collection::fromFile(__DIR__ . '/input.txt')
    ->lines()
    ->flatmap(
        static function (string $line): Generator {
            [$direction, $steps] = sscanf($line, "%s %d");

            for ($i = 0; $i < $steps; $i++) {
                yield $direction;
            }
        }
    )
    ->map(
        static fn (string $line): array =>
            match ($line) {
                'R' => [1, 0],
                'U' => [0, 1],
                'L' => [-1, 0],
                'D' => [0, -1],
            }
        )
    ->reduce(
        static function (array $snake, array $coord) use ($move, &$visited): array {
            $snake = $move($snake, $coord);

            $visited[1][sprintf("(%s,%s)", ...$snake[1])] = true;
            $visited[9][sprintf("(%s,%s)", ...$snake[9])] = true;

            return $snake;
        },
        array_pad([], 10, [0, 0])
    );

dump("Tail1: ". count($visited[1]));
dump("Tail9: " . count($visited[9]));
