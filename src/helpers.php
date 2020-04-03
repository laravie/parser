<?php

namespace Laravie\Parser;

use Closure;
use Illuminate\Support\Collection;
use Tightenco\Collect\Support\Arr;
use Tightenco\Collect\Support\Collection as Collect;

/**
 * Get an item from an array or object using "dot" notation.
 *
 * @param  mixed   $target
 * @param  string|array  $key
 * @param  mixed   $default
 *
 * @return mixed
 */
function data_get($target, $key, $default = null)
{
    if (\is_null($key)) {
        return $target;
    }

    $key = \is_array($key) ? $key : \explode('.', $key);

    foreach ($key as $i => $segment) {
        unset($key[$i]);

        if (\is_null($segment)) {
            return $target;
        }

        if ($segment === '*') {
            if ($target instanceof Collection || $target instanceof Collect) {
                $target = $target->all();
            } elseif (! \is_array($target)) {
                return value($default);
            }

            $result = Arr::pluck($target, $key);

            return \in_array('*', $key) ? Arr::collapse($result) : $result;
        }

        if (Arr::accessible($target) && Arr::exists($target, $segment)) {
            $target = $target[$segment];
        } elseif (\is_object($target) && isset($target->{$segment})) {
            $target = $target->{$segment};
        } else {
            return value($default);
        }
    }

    return $target;
}

/**
 * Get alias unless same as compared with.
 *
 * @param  string  $alias
 * @param  string  $compared
 *
 * @return string|null
 */
function alias_get($alias, $compared = null)
{
    return $alias != $compared ? $alias : null;
}

/**
 * Get an item from an object using "dot" notation.
 *
 * @param  object  $object
 * @param  string  $key
 * @param  mixed   $default
 *
 * @return mixed
 */
function object_get($object, string $key, $default = null)
{
    if (\is_null($key) || \trim($key) == '') {
        return $object;
    }

    foreach (\explode('.', $key) as $segment) {
        if (! \is_object($object) || ! isset($object->{$segment})) {
            return value($default);
        }

        $object = $object->{$segment};
    }

    return $object;
}

/**
 * Return the default value of the given value.
 *
 * @param  mixed  $value
 *
 * @return mixed
 */
function value($value)
{
    return $value instanceof Closure ? $value() : $value;
}
