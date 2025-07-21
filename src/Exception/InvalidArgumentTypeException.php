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

final class InvalidArgumentTypeException extends \InvalidArgumentException
{
    public function __construct(string $expected, mixed $actual, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(
            \sprintf(
                'Expected argument of type "%s", "%s" given',
                $expected,
                \get_debug_type($actual),
            ),
            $code,
            $previous
        );
    }
}
