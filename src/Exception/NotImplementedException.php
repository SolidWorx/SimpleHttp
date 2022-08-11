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

use Throwable;

use function sprintf;

final class NotImplementedException extends \InvalidArgumentException
{
    public function __construct(string $method, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('The method "%s" is not implemented', $method), $code, $previous);
    }
}
