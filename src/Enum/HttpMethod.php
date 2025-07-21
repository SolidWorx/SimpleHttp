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
 * HTTP Method enum
 */
enum HttpMethod: string
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case PATCH = 'PATCH';
    case OPTIONS = 'OPTIONS';
    case DELETE = 'DELETE';
    case HEAD = 'HEAD';
    case TRACE = 'TRACE';
    case CONNECT = 'CONNECT';

    public static function nonCachableMethods(): array
    {
        return [
            self::POST,
            self::PUT,
            self::PATCH,
            self::DELETE,
        ];
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
