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
use SolidWorx\SimpleHttp\Progress;
use SolidWorx\SimpleHttp\RequestOptions;
use function array_merge_recursive;
use function is_string;

final class Guzzle7Factory implements HttpAsyncClientFactory
{
    public static function createInstance(RequestOptions $requestOptions, ?ClientInterface $client = null): Guzzle7Client
    {
        $options = [
            'stream' => true,
        ];

        if ($requestOptions->onProgress !== null) {
            $options['progress'] = static function (int $totalSize, int $downloaded) use ($requestOptions): void {
                ($requestOptions->onProgress)(new Progress($downloaded, $totalSize));
            };
        }

        if ($requestOptions->buffer !== null) {
            if (is_string($requestOptions->buffer)) {
                $resource = Utils::streamFor(Utils::tryFopen($requestOptions->buffer, 'c+b'));
            } else {
                $resource = Utils::streamFor($requestOptions->buffer);
            }

            unset($options['stream']);
            $options['sink'] = $resource;
        }

        if ($client instanceof Client) {
            // @phpstan-ignore-next-line @psalm-ignore-next-line
            return Guzzle7Client::createWithConfig(array_merge_recursive($options, $client->getConfig())); // getConfig is deprecated and will be removed in Guzzle 8
        }

        return Guzzle7Client::createWithConfig($options);
    }
}
