<?php

declare(strict_types=1);

namespace App;

use Generator;
use loophp\collection\Collection;

include __DIR__ . '/vendor/autoload.php';

$input = trim(file_get_contents(__DIR__ . '/input.txt'));

$normalize  = static fn (string $hand): int =>
    match ($hand) {
        'A', 'X' => 0, // Rock
        'B', 'Y' => 1, // Paper
        'C', 'Z' => 2, // Scissors
    };

$getMatchScore = static fn (int $left, int $right): int =>
    $left === $right
        ? 3
        : ((($left + 1) % 3) === $right ? 0 : 6);

$getGameScore = static fn (int $left, int $right): int =>
    1 + $right + $getMatchScore($right, $left);

$preprocess = static fn (int $left, int $right): array =>
    [
        $left,
        match ($right) {
            0 => ($left+2)%3,
            1 => $left,
            2 => ($left+1)%3,
        }
    ];

$c = Collection::fromString($input, "\n")
    ->flatMap(
        static fn (string $line): Generator => yield from explode(' ', $line)
    )
    ->map(static fn (string $letter): int => $normalize($letter))
    ->chunk(2)
    ->map(
        static fn (array $game): array => $preprocess(...$game)
    )
    ->map(
        static fn (array $game): int => $getGameScore(...$game)
    )
    ->reduce(
        static fn (int $c, int $v): int => $c + $v,
        0
    );

dump($c);
