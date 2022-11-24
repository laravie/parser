<?php

namespace Laravie\Parser;

use Closure;

/**
 * Get alias unless same as compared with.
 *
 * @param  string  $alias
 * @param  string  $compared
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
 * @param  string|null  $key
 * @param  mixed  $default
 * @return mixed
 */
function object_get($object, ?string $key, $default = null)
{
    if (\is_null($key) || trim($key) == '') {
        return $object;
    }

    foreach (explode('.', $key) as $segment) {
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
 * @return mixed
 */
function value($value)
{
    return $value instanceof Closure ? $value() : $value;
}
