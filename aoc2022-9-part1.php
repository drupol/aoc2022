<?php

declare(strict_types=1);

namespace App;

use Generator;
use loophp\collection\Collection;

include __DIR__ . '/vendor/autoload.php';

$x = $y = 0;

$addCoords = static function (array $coord1, array $coord2): array {
    return [$coord1[0] + $coord2[0], $coord1[1] + $coord2[1]];
};

$distance = static function (array $coord1, array $coord2): int {
    $dx = $coord1[0] - $coord2[0];
    $dy = $coord1[1] - $coord2[1];

    return (int) (abs(($dx ** 2) + ($dy ** 2))) ** .5;
};

$head = $tail = [0,0];

$visited = Collection::fromFile(__DIR__ . '/input.txt')
    ->lines()
    ->flatmap(
        static function(string $line): Generator {
            [$direction, $steps] = sscanf($line, "%s %d");

            for ($i = 0; $i < $steps; $i++) {
                yield sprintf("%s %d", $direction, 1);
            }
        }
    )
    ->map(
        static function(string $line): array {
            [$direction, $steps] = sscanf($line, "%s %d");

            return match($direction) {
                'R' => [$steps, 0],
                'U' => [0, $steps],
                'L' => [-1 * $steps, 0],
                'D' => [0, -1 * $steps],
            };
        }
    )
    ->reduce(
        static function (array $visited, array $newCoord) use ($addCoords, &$head, &$tail, $distance): array {
            $newHeadPosition = $addCoords($head, $newCoord);
            $distanceBetweenHeadAndTail = $distance($tail, $newHeadPosition);

            if ($distanceBetweenHeadAndTail > 1) {
                $visited[] = sprintf("(%s,%s)", ...$head);
                $tail = $head;
            }

            $head = $newHeadPosition;

            return $visited;
        },
        $visited
    );

dump(count(array_unique($visited)));
