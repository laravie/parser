<?php

namespace Laravie\Parser\Xml;

use SimpleXMLElement;
use Illuminate\Support\Arr;
use Laravie\Parser\Document as BaseDocument;

class Document extends BaseDocument
{
    /**
     * Available namespaces.
     *
     * @var array|null
     */
    protected $namespaces;

    /**
     * Rebase document node.
     *
     * @param  string|null  $base
     *
     * @return $this
     */
    public function rebase($base = null)
    {
        $this->content = data_get($this->getOriginalContent(), $base);

        return $this;
    }

    /**
     * Set document namespace and parse the XML.
     *
     * @param  string  $namespace
     * @param  array  $schema
     * @param  array  $config
     *
     * @return array
     */
    public function namespaced($namespace, array $schema, array $config = [])
    {
        $document = $this->getContent();
        $namespaces = $this->getAvailableNamespaces();

        if (! is_null($namespace) && isset($namespaces[$namespace])) {
            $document = $document->children($namespaces[$namespace]);
        }

        $this->content = $document;

        return $this->parse($schema, $config);
    }

    /**
     * {@inheritdoc}
     */
    protected function getValue($content, $use, $default = null)
    {
        if (preg_match('/^(.*)\[(.*)\]$/', $use, $matches) && $content instanceof SimpleXMLElement) {
            return $this->getValueCollection($content, $matches, $default);
        } elseif (strpos($use, '::') !== false && $content instanceof SimpleXMLElement) {
            return $this->getValueAttribute($content, $use, $default);
        }

        return $this->getValueData($content, $use, $default);
    }

    /**
     * Cast value to string only when it is an instance of SimpleXMLElement.
     *
     * @param  mixed  $value
     *
     * @return mixed
     */
    protected function castValue($value)
    {
        if ($value instanceof SimpleXMLElement) {
            $value = (string) $value;
        }

        return $value;
    }

    /**
     * Resolve value by uses as attribute.
     *
     * @param  \SimpleXMLElement  $content
     * @param  string  $use
     * @param  mixed  $default
     *
     * @return mixed
     */
    protected function getValueAttribute(SimpleXMLElement $content, $use, $default = null)
    {
        return $this->castValue($this->getRawValueAttribute($content, $use, $default));
    }

    /**
     * Resolve value by uses as attribute as raw.
     *
     * @param  \SimpleXMLElement  $content
     * @param  string  $use
     * @param  mixed  $default
     *
     * @return mixed
     */
    protected function getRawValueAttribute(SimpleXMLElement $content, $use, $default = null)
    {
        list($value, $attribute) = explode('::', $use, 2);

        if (! empty($value)) {
            if (is_null($parent = object_get($content, $value))) {
                return $default;
            }
        } else {
            $parent = $content;
        }

        $attributes = $parent->attributes();

        return data_get($attributes, $attribute, $default);
    }

    /**
     * Resolve value by uses as data.
     *
     * @param  \SimpleXMLElement  $content
     * @param  string  $use
     * @param  mixed  $default
     *
     * @return mixed
     */
    protected function getValueData(SimpleXMLElement $content, $use, $default = null)
    {
        $value = $this->castValue(data_get($content, $use));

        if (empty($value) && ! in_array($value, ['0'])) {
            return $default;
        }

        return $value;
    }

    /**
     * Resolve values by collection.
     *
     * @param  \SimpleXMLElement  $content
     * @param  array  $matches
     * @param  mixed  $default
     *
     * @return array
     */
    protected function getValueCollection(SimpleXMLElement $content, array $matches, $default = null)
    {
        $parent = $matches[1];
        $namespace = null;

        if (strpos($parent, '/') !== false) {
            list($parent, $namespace) = explode('/', $parent, 2);
        }

        $collection = data_get($content, $parent);
        $namespaces = $this->getAvailableNamespaces();

        //$uses = explode(',', $matches[2]);
        // use preg_split to split all comma ignoring
        // the commas inside curly brackets
        $uses = preg_split('/(,)(?=(?:[^}]|{[^{]*})*$)/',$matches[2]);
        $values = [];

        if (! $collection instanceof SimpleXMLElement) {
            return $default;
        }

        foreach ($collection as $content) {
            if (empty($content)) {
                continue;
            }

            if (! is_null($namespace) && isset($namespaces[$namespace])) {
                $content = $content->children($namespaces[$namespace]);
            }

            $values[] = $this->parseValueCollection($content, $uses);
        }

        return $values;
    }

    /**
     * Resolve values by collection.
     *
     * @param  \SimpleXMLElement  $content
     * @param  array  $uses
     *
     * @return array
     */
    protected function parseValueCollection(SimpleXMLElement $content, array $uses)
    {
        $value = [];

        foreach ($uses as $use) {
            if (preg_match("/^(.*)\{(.*)\}(\>(.*)|)/", $use, $output_array))
            {
                $thirdArrayName = $output_array[1];
                $thirdArrayData = $output_array[2];
                $thirdArrayOutputName = (isset($output_array[4]))? $output_array[4]:$thirdArrayName;
                $usesThird = explode(',', $thirdArrayData);
                $value[$thirdArrayOutputName] = $this->parseValueCollectionThird($content,$thirdArrayName,$usesThird);
            }
            else
            {
                list($name, $as) = strpos($use, '>') !== false ? explode('>', $use, 2) : [$use, $use];

                if (preg_match('/^([A-Za-z0-9_\-\.]+)\((.*)\=(.*)\)$/', $name, $matches)) {
                    if ($name == $as) {
                        $as = null;
                    }

                    $item = $this->getSelfMatchingValue($content, $matches, $as);

                    if (is_null($as)) {
                        $value = array_merge($value, $item);
                    } else {
                        Arr::set($value, $as, $item);
                    }
                }
                else
                {
                    if ($name == '@') {
                        $name = null;
                    }
                    Arr::set($value, $as, $this->getValue($content, $name));
                }
            }
        }

        return $value;
    }

    /**
     * Resolve values by collection for third level.
     *
     * @param  \SimpleXMLElement  $content
     * @param  string  $thirdArrayName
     * @param  array  $uses
     *
     * @return array
     */
    protected function parseValueCollectionThird(SimpleXMLElement $content,$thirdArrayName, array $uses)
    {
        $value = [];

        foreach ($uses as $use)
        {
            list($name, $as) = strpos($use, '>') !== false ? explode('>', $use, 2) : [$use, $use];
            if (preg_match('/^([A-Za-z0-9_\-\.]+)\((.*)\=(.*)\)$/', $name, $matches)) {
                if ($name == $as) {
                    $as = null;
                }

                $item = $this->getSelfMatchingValue($content->{$thirdArrayName}, $matches, $as);

                if (is_null($as)) {
                    $value = array_merge($value, $item);
                } else {
                    Arr::set($value, $as, $item);
                }
            }
            else
            {
                if ($name == '@') {
                    $name = null;
                }
                Arr::set($value, $as, $this->getValue($content->$thirdArrayName, $name));
            }
        }

        return $value;
    }


    /**
     * Get self matching value.
     *
     * @param  \SimpleXMLElement  $content
     * @param  array  $matches
     * @param  string|null  $alias
     *
     * @return array
     */
    protected function getSelfMatchingValue(SimpleXMLElement $content, array $matches = [], $alias = null)
    {
        $name = $matches[1];
        $key = $matches[2];
        $meta = $matches[3];

        $item = [];
        $uses = ($key == '@' ? $meta : "{$key},{$meta}");

        if (is_null($alias)) {
            $alias = $name;
        }

        $collection = $this->getValue($content, sprintf('%s[%s]', $name, $uses));

        foreach ((array) $collection as $collect) {
            $v = $collect[$meta];

            if ($key == '@') {
                $item[$alias][] = $v;
            } else {
                $item[$collect[$key]] = $v;
            }
        }

        return $item;
    }

    /**
     * Get available namespaces, and cached it during runtime to avoid
     * overhead.
     *
     * @return array|null
     */
    protected function getAvailableNamespaces()
    {
        if (is_null($this->namespaces)) {
            $this->namespaces = $this->getOriginalContent()->getNameSpaces(true);
        }

        return $this->namespaces;
    }
}
