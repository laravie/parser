<?php

namespace Laravie\Parser\Xml\Concerns;

use SimpleXMLElement;
use Tightenco\Collect\Support\Arr;
use function Laravie\Parser\alias_get;
use Laravie\Parser\Xml\Definitions\MultiLevel;

trait SupportMultiLevel
{
    /**
     * prepare use variable for using.
     *
     * @param  string  $use
     *
     * @return \Laravie\Parser\Xml\Definitions\MultiLevel|string
     */
    protected function resolveUses(string $uses)
    {
        $result = $this->parseAdvancedUses($uses);

        return ! empty($result->getRoot()) ? $result : $uses;
    }

    /**
     * split the use.
     *
     * @param  string  $value
     *
     * @return array
     */
    protected function parseBasicUses(string $value): array
    {
        $level = 0;
        $uses = [''];
        $current = 0;

        foreach (\str_split($value) as $char) {
            switch ($char) {
                case '{':
                    $level++;
                    $uses[$current] .= '{';
                    break;
                case '}':
                    $level--;
                    $uses[$current] .= '}';
                    break;
                case ',':
                    if ($level == 0) {
                        ++$current;
                        $uses[$current] = '';
                        break;
                    }
                    // no break
                default:
                    $uses[$current] .= $char;
            }
        }

        return $uses;
    }

    /**
     * split the use.
     *
     * @param  string  $value
     *
     * @return \Laravie\Parser\Xml\Definitions\MultiLevel
     */
    protected function parseAdvancedUses(string $value): MultiLevel
    {
        $level = 0;
        $uses = [''];
        $current = 0;
        $root = '';

        foreach (\str_split($value) as $char) {
            switch ($char) {
                case '{':
                    if ($level == 0) {
                        $root = $uses[0];
                        $uses[$current] = '';
                        ++$level;
                        break;
                    } else {
                        $uses[$current] .= '{';
                        ++$level;
                        break;
                    }
                    // no break
                case ',':
                    if ($level === 1) {
                        ++$current;
                        $uses[$current] = '';
                        break;
                    } else {
                        $uses[$current] .= ',';
                        break;
                    }
                    // no break
                case '}':
                    if ($level === 2) {
                        $uses[$current] .= '}';
                        --$level;
                        break;
                    } elseif ($level === 1) {
                        ++$current;
                        $uses[$current] = '';
                        --$level;
                        break;
                    }
                    // no break
                default:
                    $uses[$current] .= $char;
            }
        }

        $alias = $uses[$current] ? \str_replace('>', '', $uses[$current]) : $root;

        \array_pop($uses);

        return new MultiLevel($root, $alias, $uses);
    }

    /**
     * Resolve values by collection of multi levels.
     *
     * @param  \SimpleXMLElement  $content
     * @param  \Laravie\Parser\Xml\Definitions\MultiLevel  $multilevel
     *
     * @return array
     */
    protected function parseMultiLevelsValueCollection(SimpleXMLElement $content, MultiLevel $multilevel): array
    {
        $value = [];
        $result = [];
        $features = $content->{$multilevel->getRoot()};


        if (! empty($features)) {
            foreach ($features as $key => $feature) {
                foreach ($multilevel as $use) {
                    if (\strpos($use, '{') !== false) {
                        $secondary = $this->resolveUses($use);

                        $value[$secondary->getKey()] = $this->parseMultiLevelsValueCollection($feature, $secondary);
                    } else {
                        [$name, $as] = \strpos($use, '>') !== false ? \explode('>', $use, 2) : [$use, $use];

                        if (\preg_match('/^([A-Za-z0-9_\-\.]+)\((.*)\=(.*)\)$/', $name, $matches)) {
                            $as = alias_get($as, $name);

                            $item = $this->getSelfMatchingValue($feature, $matches, $as);

                            if (\is_null($as)) {
                                $value = \array_merge($value, $item);
                            } else {
                                Arr::set($value, $as, $item);
                            }
                        } else {
                            $name = alias_get($name, '@');

                            Arr::set($value, $as, $this->getValue($feature, $name));
                        }
                    }
                }

                $result[] = $value;
            }
        }

        return $result;
    }
}
