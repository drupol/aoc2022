<?php

declare(strict_types=1);

namespace App;

use loophp\collection\Collection;

include __DIR__ . '/vendor/autoload.php';

$input = trim(file_get_contents(__DIR__ . '/input.txt'));

$c = Collection::fromString($input, "\n\n")
    ->map(static fn (string $lines): array => explode("\n", $lines))
    ->map(static fn (array $lines): int => array_sum($lines))
    ->sort()
    ->reverse()
    ->all();

dump($c);
