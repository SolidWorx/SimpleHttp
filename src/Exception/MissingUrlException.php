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

final class MissingUrlException extends \RuntimeException
{
    public function __construct(int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct('The "url" option is missing. Ensure you set the URL with the `$httpClient->url()` method.', $code, $previous);
    }
}
