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

namespace SolidWorx\SimpleHttp\Discovery;

use Http\Client\Common\EmulatedHttpAsyncClient;
use Http\Client\HttpAsyncClient;
use Http\Discovery\ClassDiscovery;
use Http\Discovery\Exception\DiscoveryFailedException;
use Http\Discovery\Exception\NotFoundException;
use Psr\Http\Client\ClientInterface;
use SolidWorx\SimpleHttp\Discovery\Strategy\HttpAsyncClientStrategy;
use SolidWorx\SimpleHttp\Factory\HttpAsyncClientFactory;
use SolidWorx\SimpleHttp\RequestOptions;
use function array_unshift;
use function array_unique;
use function assert;
use function is_a;

final class HttpAsyncClientDiscovery extends ClassDiscovery
{
    public static function find(RequestOptions $requestOptions, ?ClientInterface $client = null): HttpAsyncClient
    {
        /** @var list<class-string> $strategies */
        $strategies = self::getStrategies();
        array_unshift($strategies, HttpAsyncClientStrategy::class);
        self::setStrategies(array_unique($strategies));

        try {
            $clientFactory = self::findOneByType(HttpAsyncClient::class);
        } catch (DiscoveryFailedException $e) {
            throw new NotFoundException('No HTTP async clients found. Make sure to install a package providing "php-http/async-client-implementation". Example: "composer require php-http/guzzle7-adapter".', 0, $e);
        }

        if (is_a($clientFactory, HttpAsyncClientFactory::class, true)) {
            /** @var HttpAsyncClientFactory $clientFactory */
            return $clientFactory::createInstance($requestOptions, $client);
        }

        if ($client !== null) {
            return self::getHttpAsyncClient($client);
        }

        $httpClient = self::instantiateClass($clientFactory);

        assert($httpClient instanceof HttpAsyncClient || $httpClient instanceof ClientInterface);

        return self::getHttpAsyncClient($httpClient);
    }

    /**
     * @param HttpAsyncClient|ClientInterface $client
     */
    private static function getHttpAsyncClient($client): HttpAsyncClient
    {
        if (!$client instanceof HttpAsyncClient) {
            return new EmulatedHttpAsyncClient($client);
        }

        return $client;
    }
}
