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

namespace SolidWorx\SimpleHttp\Enum;

/**
 * HTTP Version enum
 */
enum HttpVersion: string
{
    case HTTP_1_1 = '1.1';
    case HTTP_2_0 = '2.0';

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
