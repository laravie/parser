<?php

namespace Laravie\Parser\Xml\Concerns;

use Laravie\Parser\Xml\Definitions\MultiLevel;

trait UsesParser
{
    /**
     * prepare use variable for using.
     *
     * @param  string  $use
     *
     * @return \Laravie\Parser\Xml\Definitions\MultiLevel|string
     */
    protected function resolveUses($uses)
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
    protected function parseBasicUses($value)
    {
        $level = 0;
        $uses = [''];
        $current = 0;

        foreach (str_split($value) as $char) {
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
    protected function parseAdvancedUses($value)
    {
        $level = 0;
        $uses = [''];
        $current = 0;
        $root = '';

        foreach (str_split($value) as $char) {
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

        $alias = $uses[$current] ? str_replace('>', '', $uses[$current]) : $root;

        array_pop($uses);

        return new MultiLevel($root, $alias, $uses);
    }
}
