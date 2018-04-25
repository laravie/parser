<?php

namespace Laravie\Parser;

/**
 * Get alias unless same as compared with.
 *
 * @param  string  $alias
 * @param  string  $compared
 * @return string|null
 */
function get_alias($alias, $compared = null) {
    return $alias != $compared ? $alias : null;
}
