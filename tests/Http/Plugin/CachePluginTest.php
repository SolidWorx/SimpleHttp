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

namespace SolidWorx\SimpleHttp\Tests\Http\Plugin;

use Http\Promise\FulfilledPromise;
use Http\Promise\Promise;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SolidWorx\SimpleHttp\Http\Plugin\CachePlugin;

/**
 * @coversDefaultClass \SolidWorx\SimpleHttp\Http\Plugin\CachePlugin
 */
final class CachePluginTest extends TestCase
{
    public function testCachePlugin(): void
    {
        $cacheItemPool = $this->createMock(CacheItemPoolInterface::class);
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $request = new Request('GET', 'https://example.com');
        $response = new Response(200, [], 'Hello World');

        $cacheItemPool->expects(self::once())
            ->method('getItem')
            ->with(md5('GEThttps://example.com'))
            ->willReturn($cacheItem);

        $cacheItemPool->expects(self::once())
            ->method('save')
            ->with($cacheItem);

        $cacheItem->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $cacheItem->expects(self::never())
            ->method('expiresAfter');

        $cacheItem->expects(self::once())
            ->method('set')
            ->with($response);

        $plugin = new CachePlugin($cacheItemPool);

        $pluginResponse = $plugin->handleRequest(
            $request,
            function (RequestInterface $nextRequest) use ($request, $response): Promise {
                self::assertSame($request, $nextRequest);

                return new FulfilledPromise($response);
            },
            fn (RequestInterface $nextRequest): Promise => new FulfilledPromise($response)
        );

        $pluginResponse->then(
            fn (ResponseInterface $nextResponse) => self::assertSame($response, $nextResponse)
        )->wait();
    }

    public function testCachePluginWithTtl(): void
    {
        $cacheItemPool = $this->createMock(CacheItemPoolInterface::class);
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $request = new Request('GET', 'https://example.com');
        $response = new Response(200, [], 'Hello World');

        $cacheItemPool->expects(self::once())
            ->method('getItem')
            ->with(md5('GEThttps://example.com'))
            ->willReturn($cacheItem);

        $cacheItemPool->expects(self::once())
            ->method('save')
            ->with($cacheItem);

        $cacheItem->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $cacheItem->expects(self::once())
            ->method('expiresAfter')
            ->with(100);

        $cacheItem->expects(self::once())
            ->method('set')
            ->with($response);

        $plugin = new CachePlugin($cacheItemPool, 100);

        $pluginResponse = $plugin->handleRequest(
            $request,
            function (RequestInterface $nextRequest) use ($request, $response): Promise {
                self::assertSame($request, $nextRequest);

                return new FulfilledPromise($response);
            },
            fn (RequestInterface $nextRequest): Promise => new FulfilledPromise($response)
        );

        $pluginResponse->then(
            fn (ResponseInterface $nextResponse) => self::assertSame($response, $nextResponse)
        )->wait();
    }

    public function testCachePluginWithCacheHit(): void
    {
        $cacheItemPool = $this->createMock(CacheItemPoolInterface::class);
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $request = new Request('GET', 'https://example.com');
        $response = new Response(200, [], 'Hello World');

        $cacheItemPool->expects(self::once())
            ->method('getItem')
            ->with(md5('GEThttps://example.com'))
            ->willReturn($cacheItem);

        $cacheItemPool->expects(self::never())
            ->method('save');

        $cacheItem->expects(self::once())
            ->method('isHit')
            ->willReturn(true);

        $cacheItem->expects(self::never())
            ->method('expiresAfter');

        $cacheItem->expects(self::never())
            ->method('set');

        $cacheItem->expects(self::once())
            ->method('get')
            ->willReturn($response);

        $plugin = new CachePlugin($cacheItemPool, 100);

        $pluginResponse = $plugin->handleRequest(
            $request,
            function (RequestInterface $nextRequest): Promise {
                self::fail('Request should not be called when cache hit');
            },
            function (RequestInterface $nextRequest): Promise {
                self::fail('Request should not be called when cache hit');
            },
        );

        $pluginResponse->then(
            fn (ResponseInterface $nextResponse) => self::assertSame($response, $nextResponse)
        )->wait();
    }

    public function testCachePluginWithCacheException(): void
    {
        $cacheItemPool = $this->createMock(CacheItemPoolInterface::class);
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $request = new Request('GET', 'https://example.com');
        $response = new Response(200, [], 'Hello World');

        $cacheItemPool->expects(self::once())
            ->method('getItem')
            ->with(md5('GEThttps://example.com'))
            ->willThrowException(new class extends \Exception implements InvalidArgumentException{});

        $cacheItemPool->expects(self::never())
            ->method('save');

        $cacheItem->expects(self::never())
            ->method('isHit');

        $cacheItem->expects(self::never())
            ->method('expiresAfter');

        $cacheItem->expects(self::never())
            ->method('set');

        $cacheItem->expects(self::never())
            ->method('get');

        $plugin = new CachePlugin($cacheItemPool, 100);

        $pluginResponse = $plugin->handleRequest(
            $request,
            function (RequestInterface $nextRequest) use ($request, $response): Promise {
                self::assertSame($request, $nextRequest);

                return new FulfilledPromise($response);
            },
            fn (RequestInterface $nextRequest): Promise => new FulfilledPromise($response)
        );

        $pluginResponse->then(
            fn (ResponseInterface $nextResponse) => self::assertSame($response, $nextResponse)
        )->wait();
    }
}
