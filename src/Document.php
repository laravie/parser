<?php namespace Laravie\Parser;

use Underscore\Types\Strings;

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
     *
     * @return array
     */
    public function parse(array $schema, array $config = [])
    {
        $output = [];

        foreach ($schema as $key => $data) {
            $value  = $this->parseData($data);
            $ignore = isset($config['ignore']) ? $config['ignore'] : false;

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
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content         = $content;
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
     * @param  mixed   $value
     * @param  string  $filter
     *
     * @return mixed
     */
    protected function filterValue($value, $filter)
    {
        $resolver = $this->getFilterResolver($filter);

        if (method_exists($resolver[0], $resolver[1])) {
            return $this->callFilterResolver($resolver, $value);
        }

        return $value;
    }

    /**
     * Resolve value from content.
     *
     * @param  array   $config
     * @param  string  $hash
     *
     * @return mixed
     */
    protected function resolveValue(array $config, $hash)
    {
        if (! isset($config['uses'])) {
            return isset($config['default']) ? $config['default'] : null;
        }

        if (! is_array($config['uses'])) {
            return $this->getValue($this->getContent(), $config['uses'], $hash);
        }

        $values = [];

        foreach ($config['uses'] as $use) {
            $values[] = $this->getValue($this->getContent(), $use, $hash);
        }

        return $values;
    }

    /**
     * Resolve value from uses filter.
     *
     * @param  mixed   $content
     * @param  string  $use
     * @param  string  $default
     *
     * @return mixed
     */
    abstract protected function getValue($content, $use, $default = null);

    /**
     * Get filter resolver.
     *
     * @param  string  $filter
     *
     * @return array
     */
    protected function getFilterResolver($filter)
    {
        $class  = $filter;
        $method = 'filter';

        $position = strpos($filter, '@');

        if ($position === 0) {
            $method = 'filter'.ucwords(substr($filter, 1));
            return [$this, $method];
        }

        if ($position !== false) {
            list($class, $method) = explode('@', $filter, 2);
        }

        return $this->makeFilterResolver($class, $method);
    }

    /**
     * Parse single data.
     *
     * @param  mixed  $data
     *
     * @return mixed
     */
    protected function parseData($data)
    {
        $hash   = Strings::random(60);
        $value  = $data;
        $filter = null;

        if (is_array($data)) {
            $value  = $this->resolveValue($data, $hash);
            $filter = isset($data['filter']) ? $data['filter'] : null;
        }

        if ($value === $hash) {
            $value = isset($data['default']) ? $data['default'] : null;
        }

        if (! is_null($filter)) {
            $value = $this->filterValue($value, $filter);
        }

        return $value;
    }

    /**
     * Make filter resolver.
     *
     * @param  array  $class
     * @param  mixed  $method
     *
     * @return array
     */
    protected function makeFilterResolver($class, $method)
    {
        $class = new $class();

        return [$class, $method];
    }

    /**
     * Call filter to parse the value.
     *
     * @param  array  $resolver
     * @param  mixed  $value
     *
     * @return mixed
     */
    protected function callFilterResolver($resolver, $value)
    {
        return call_user_func($resolver, $value);
    }
}
