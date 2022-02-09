<?php

declare(strict_types=1);

/*
 * This file is part of SolidWorx/SimpleHttp project.
 *
 * Copyright (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SolidWorx\SimpleHttp\Exception;

use function function_exists;
use function get_class;
use function get_debug_type;
use function gettype;
use function is_object;

final class InvalidArgumentTypeException extends \InvalidArgumentException
{
    /**
     * @param mixed $actual
     */
    public function __construct(string $expected, $actual, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(
            \sprintf(
                'Expected argument of type "%s", "%s" given',
                $expected,
                function_exists('get_debug_type') ?
                get_debug_type($actual) :
                (is_object($actual) ? get_class($actual) : gettype($actual))
            ),
            $code,
            $previous
        );
    }
}
