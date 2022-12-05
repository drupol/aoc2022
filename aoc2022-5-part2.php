<?php

declare(strict_types=1);

namespace App;

use Generator;
use loophp\collection\Collection;
use SplStack;

include __DIR__ . '/vendor/autoload.php';

$input = trim(file_get_contents(__DIR__ . '/input.txt'));

$strToStack = static function (array $data): SplStack {
    $stack = new SplStack;
    $stack->setIteratorMode(SplStack::IT_MODE_LIFO);

    foreach ($data as $letter) {
        $stack[] = $letter;
    }

    return $stack;
};

$c = Collection::fromString($input)
    ->lines()
    ->until(static fn (string $line): bool => "" === $line)
    ->compact()
    ->init()
    ->map(
        static fn (string $line): array => str_split($line, 4)
    )
    ->map(
        static fn (array $data): array => array_pad($data, 9, '')
    )
    ->map(
        static fn (array $data): array => array_map('trim', $data)
    )
    ->unwrap()
    ->map(
        static fn (string $data): string => preg_replace( '/[\W]/', '', $data)
    )
    ->chunk(9)
    ->transpose()
    ->map(
        static fn (array $data): array => array_filter($data)
    )
    ->map(
        static fn (array $data): array => array_reverse($data)
    )
    ->map(
        static fn (array $line): SplStack => $strToStack($line)
    );

$stacks = $c->all();

$c = Collection::fromString($input)
    ->lines()
    ->since(
        static fn (string $line): bool => "" === $line
    )
    ->drop(1)
    ->map(
        static function (string $line): array {
            return sscanf($line, "move %d from %d to %d");
        }
    )
    ->apply(
        static function (array $input) use ($stacks): void {
            list($amount, $from, $to) = $input;

            $tmp = [];
            for ($i = 0; $i < $amount; $i++) {
                $tmp[] = $stacks[$from-1]->pop();
            }

            foreach (array_reverse($tmp) as $letter) {
                $stacks[$to-1]->push($letter);
            }
        }
    )
    ->squash();

$str = array_reduce(
    $stacks,
    static function (string $carry, SplStack $stack): string {
        return $carry . $stack->top();
    },
    ''
);

dump($str);
