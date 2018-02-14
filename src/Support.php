<?php

namespace Laravie\Parser;

use Closure;
use Illuminate\Support\Collection;
use Tightenco\Collect\Support\Arr;
use Tightenco\Collect\Support\Collection as Collect;

class Support
{
    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param  mixed   $target
     * @param  string|array  $key
     * @param  mixed   $default
     *
     * @return mixed
     */
    public static function fromData($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (! is_null($segment = array_shift($key))) {
            if ($segment === '*') {
                if ($target instanceof Collection || $target instanceof Collect) {
                    $target = $target->all();
                } elseif (! is_array($target)) {
                    return static::value($default);
                }
                $result = Arr::pluck($target, $key);

                return in_array('*', $key) ? Arr::collapse($result) : $result;
            }

            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return static::value($default);
            }
        }

        return $target;
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
    public static function fromObject($object, string $key, $default = null)
    {
        if (is_null($key) || trim($key) == '') {
            return $object;
        }

        foreach (explode('.', $key) as $segment) {
            if (! is_object($object) || ! isset($object->{$segment})) {
                return static::value($default);
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
    public static function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}
