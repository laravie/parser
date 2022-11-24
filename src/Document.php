<?php

namespace Laravie\Parser;

use Closure;

abstract class Document
{
    /**
     * The content.
     *
     * @var mixed
     */
    protected $content;

    /**
     * The original content.
     *
     * @var mixed
     */
    protected $originalContent;

    /**
     * Parse document.
     *
     * @param  array  $schema
     * @param  array  $config
     * @return array
     */
    public function parse(array $schema, array $config = []): array
    {
        $output = [];
        $ignore = $config['ignore'] ?? false;

        foreach ($schema as $key => $data) {
            $value = $this->parseData($data);

            if (! $ignore) {
                $output[$key] = $value;
            }
        }

        return $output;
    }

    /**
     * Set the content.
     *
     * @param  mixed  $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        $this->originalContent = $content;

        return $this;
    }

    /**
     * Get the content.
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get original content.
     *
     * @return mixed
     */
    public function getOriginalContent()
    {
        return $this->originalContent;
    }

    /**
     * Filter value.
     *
     * @param  mixed  $value
     * @param  \Closure|string|null  $filter
     * @return mixed
     */
    protected function filterValue($value, $filter = null)
    {
        if ($filter instanceof Closure) {
            return $this->callFilterResolver($filter, $value);
        }

        $resolver = $this->getFilterResolver((string) $filter);

        if (method_exists($resolver[0], $resolver[1])) {
            return $this->callFilterResolver($resolver, $value);
        }

        return $value;
    }

    /**
     * Resolve value from content.
     *
     * @param  array<string, mixed>  $config
     * @param  string  $hash
     * @return mixed
     */
    protected function resolveValue(array $config, string $hash)
    {
        if (! isset($config['uses'])) {
            return $config['default'] ?? null;
        }

        /** @var array<int, string|null>|string|null $uses */
        $uses = $config['uses'];

        if (! \is_array($uses)) {
            return $this->getValue($this->getContent(), $uses, $hash);
        }

        $values = [];

        foreach ($uses as $use) {
            /** @var string|null $use */
            $values[] = $this->getValue($this->getContent(), $use, $hash);
        }

        return $values;
    }

    /**
     * Resolve value from uses filter.
     *
     * @param  mixed  $content
     * @param  string|null  $use
     * @param  string|null  $default
     * @return mixed
     */
    abstract protected function getValue($content, ?string $use, ?string $default = null);

    /**
     * Get filter resolver.
     *
     * @param  class-string|string  $filter
     * @return array{0: object, 1: string}
     */
    protected function getFilterResolver(string $filter): array
    {
        $method = 'filter';
        $position = strpos($filter, '@');

        if ($position === 0) {
            $method = 'filter'.ucwords(substr($filter, 1));

            return [$this, $method];
        }

        if ($position !== false) {
            /**
             * @var class-string $class
             * @var string $method
             */
            [$class, $method] = explode('@', $filter, 2);
        } else {
            /** @var class-string $class */
            $class = $filter;
        }

        return $this->makeFilterResolver($class, $method);
    }

    /**
     * Parse single data.
     *
     * @param  array<string, mixed>  $data
     * @return mixed
     */
    protected function parseData($data)
    {
        $hash = hash('sha256', (string) microtime(true));
        $value = $data;
        $filter = null;

        if (\is_array($data)) {
            $value = $this->resolveValue($data, $hash);
            $filter = $data['filter'] ?? null;
        }

        if ($value === $hash) {
            $value = $data['default'] ?? null;
        }

        if (! \is_null($filter)) {
            $value = $this->filterValue($value, $filter);
        }

        return $value;
    }

    /**
     * Make filter resolver.
     *
     * @param  class-string  $class
     * @param  string  $method
     * @return array{0: object, 1: string}
     */
    protected function makeFilterResolver(string $class, string $method): array
    {
        $class = new $class();

        return [$class, $method];
    }

    /**
     * Call filter to parse the value.
     *
     * @param  callable  $resolver
     * @param  mixed  $value
     * @return mixed
     */
    protected function callFilterResolver(callable $resolver, $value)
    {
        return \call_user_func($resolver, $value);
    }
}
