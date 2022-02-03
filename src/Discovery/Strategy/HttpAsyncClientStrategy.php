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

namespace SolidWorx\SimpleHttp\Discovery\Strategy;

use Http\Adapter\Guzzle7\Client as Guzzle7;
use Http\Client\HttpAsyncClient;
use Http\Discovery\Strategy\DiscoveryStrategy;
use SolidWorx\SimpleHttp\Factory\Guzzle7Factory;

final class HttpAsyncClientStrategy implements DiscoveryStrategy
{
    /**
     * @var array<class-string, list<array{class: class-string, condition: class-string}>>
     */
    private static array $classes = [
        HttpAsyncClient::class => [
            ['class' => Guzzle7Factory::class, 'condition' => Guzzle7::class],
        ],
    ];

    /**
     * @param class-string|string $type
     *
     * @return list<array{class: class-string, condition: class-string}>
     */
    public static function getCandidates($type): array
    {
        return self::$classes[$type] ?? [];
    }
}
