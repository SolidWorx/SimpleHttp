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

namespace SolidWorx\SimpleHttp\Factory;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use Http\Adapter\Guzzle7\Client as Guzzle7Client;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\StreamInterface;
use SolidWorx\SimpleHttp\Progress;
use SolidWorx\SimpleHttp\RequestOptions;

use function GuzzleHttp\Psr7\stream_for;
use function GuzzleHttp\Psr7\try_fopen;

final class Guzzle7Factory implements HttpAsyncClientFactory
{
    public static function createInstance(RequestOptions $requestOptions, ?ClientInterface $client = null): Guzzle7Client
    {
        $options = [
            'stream' => true,
        ];

        if (null !== $requestOptions->onProgress) {
            $options['progress'] = static function (int $totalSize, int $downloaded) use ($requestOptions): void {
                ($requestOptions->onProgress)(new Progress($downloaded, $totalSize));
            };
        }

        if (null !== $requestOptions->buffer) {
            if (\is_string($requestOptions->buffer)) {
                $resource = self::streamFor(self::tryFopen($requestOptions->buffer, 'c+b'));
            } else {
                $resource = self::streamFor($requestOptions->buffer);
            }

            unset($options['stream']);
            $options['sink'] = $resource;
        }

        if ($client instanceof Client) {
            // @phpstan-ignore-next-line @psalm-ignore-next-line
            return Guzzle7Client::createWithConfig(\array_merge_recursive($options, $client->getConfig())); // getConfig is deprecated and will be removed in Guzzle 8
        }

        return Guzzle7Client::createWithConfig($options);
    }

    /**
     * @return resource
     */
    private static function tryFopen(string $resource, string $mode)
    {
        if (\class_exists(Utils::class)) {
            return Utils::tryFopen($resource, $mode);
        }

        return try_fopen($resource, $mode);
    }

    /**
     * @param string|resource $stream
     */
    private static function streamFor($stream): StreamInterface
    {
        if (\class_exists(Utils::class)) {
            return Utils::streamFor($stream);
        }

        return stream_for($stream);
    }
}
