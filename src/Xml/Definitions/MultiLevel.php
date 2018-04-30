<?php

namespace Laravie\Parser\Xml\Definitions;

use ArrayIterator;
use IteratorAggregate;

class MultiLevel implements IteratorAggregate
{
    /**
     * Root definition.
     *
     * @var string
     */
    protected $root = '';

    /**
     * Alias definition.
     *
     * @var string
     */
    protected $alias = '';

    /**
     * Uses definition.
     *
     * @var array
     */
    protected $uses = [];

    /**
     * Make a new multilevel definition.
     *
     * @param  string  $root
     * @param  string  $alias
     * @param  array  $uses
     */
    public function __construct(string $root, string $alias, array $uses)
    {
        $this->root = $root;
        $this->alias = $alias;
        $this->uses = $uses;
    }

    /**
     * Get root method.
     *
     * @return string
     */
    public function getRoot(): string
    {
        return $this->root;
    }

    /**
     * Get key.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->alias;
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->uses);
    }
}
