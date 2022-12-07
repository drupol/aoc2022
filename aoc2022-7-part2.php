<?php

declare(strict_types=1);

namespace App;

use App\NodeInterface as AppNodeInterface;
use Exception;
use Generator;
use loophp\collection\Collection;
use loophp\collection\Contract\Operation\Sortable;
use SplStack;

include __DIR__ . '/vendor/autoload.php';

$input = trim(file_get_contents(__DIR__ . '/input.txt'));

enum NodeType: string {
    case DIRECTORY = 'directory';
    case FILE = 'file';
}

interface NodeInterface {
    public function add(NodeInterface $node): void;

    public function getType(): NodeType;

    public function getSize(): int;

    public function getParent(): ?NodeInterface;

    public function setParent(NodeInterface $node): void;

    public function accept(Visitor $visitor);

    public function isLeaf(): bool;

    public function children(): Generator;
}

interface Visitor {
    public function visit(NodeInterface $node);
}

final class Node implements NodeInterface {
    public array $nodes = [];

    private array $properties;

    private ?NodeInterface $parent = null;

    public function __construct(private NodeType $type, array $properties = [])
    {
        $this->properties = $properties + ['size' => 0];
    }

    public function isLeaf(): bool
    {
        return [] === $this->nodes;
    }

    public function add(NodeInterface $node): void {
        if ($this->getType() === NodeType::FILE) {
            throw new Exception('Unable to add a file to a file.');
        }

        $node->setParent($this);

        $this->nodes[] = $node;
    }

    public function getName(): string
    {
        return $this->properties['name'] ?? throw new Exception('Unknown "name" property.');
    }

    public function getType(): NodeType
    {
        return $this->type;
    }

    public function getSize(): int
    {
        if ($this->getType() === NodeType::FILE) {
            return $this->properties['size'] ?? throw new Exception('Unknown "size" property.');
        }

        return array_reduce(
            $this->nodes,
            static fn (int $carry, NodeInterface $node): int => $carry + $node->getSize(),
            0
        );
    }

    public function setParent(NodeInterface $node): void
    {
        $this->parent = $node;
    }

    public function getParent(): ?NodeInterface {
        return $this->parent;
    }

    public function children(): Generator{
        yield from $this->nodes;
    }

    public function getAncestors(): Generator
    {
        $node = $this;

        while ($node = $node->getParent()) {
            yield $node;
        }
    }

    public function getPath(): string
    {
        $names = [];

        foreach ($this->getAncestors() as $ancestor) {
            $names[] = $ancestor->getName();
        }

        return sprintf('%s/%s', implode('/', array_reverse($names)), $this->getName());
    }

    public function accept(Visitor $visitor)
    {
        return $visitor->visit($this);
    }
}

$root = new Node(NodeType::DIRECTORY, ['name' => '']);

$c = Collection::fromFile(__DIR__ . '/input.txt')
    ->lines()
    ->reduce(
        static function (NodeInterface $node, string $line): NodeInterface {
            if (str_starts_with($line, '$ cd')) {
                sscanf($line, "$ cd %s", $cd);

                if ('..' === $cd) {
                    return $node->getParent();
                }

                foreach ($node->children() as $child) {
                    if ($child->getName() === $cd) {
                        return $child;
                    }
                }

                return $node;
            }

            if (str_starts_with($line, '$ ls')) {
                return $node;
            }

            if (str_starts_with($line, 'dir ')) {
                sscanf($line, "dir %s", $dir);

                $node->add(new Node(NodeType::DIRECTORY, ['name' => $dir]));

                return $node;
            }

            sscanf($line, "%d %s", $size, $name);

            $node->add(new Node(NodeType::FILE, ['name' => $name, 'size' => $size]));

            return $node;
        },
        $root
    );

final class DirectoryVisitor implements Visitor {
    public function visit(NodeInterface $node)
    {
        $nodes = [];
        foreach ($node->children() as $child) {
            if ($child->getType() === NodeType::FILE) {
                continue;
            }

            $nodes = \array_merge(
                $nodes,
                $child->accept($this)
            );
        }

        if ($node->getType() === NodeType::DIRECTORY) {
            $nodes[] = $node;
        }

        return $nodes;
    }
}

foreach ($c->getAncestors() as $ancestor) {
    $root = $ancestor;
}

$directoryVisitor = new DirectoryVisitor;
$directories = $root->accept($directoryVisitor);

dump("Root size: ", $root->getSize());
dump("Free space: ", 70000000 - $root->getSize());
dump("Minimum space to free: ", 30000000 - (70000000 - $root->getSize()));

$spaceToFree = 30000000 - (70000000 - $root->getSize());

$c = Collection::fromIterable($directories)
    ->filter(
        static fn (NodeInterface $node): bool => $node->getSize() > $spaceToFree
    )
    ->sort(
        Sortable::BY_VALUES,
        static fn (NodeInterface $left, NodeInterface $right): int => $left->getSize() <=> $right->getSize()
    );

dump($c->first()->getSize());
