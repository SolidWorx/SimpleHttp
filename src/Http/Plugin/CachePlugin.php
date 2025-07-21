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

namespace SolidWorx\SimpleHttp\Http\Plugin;

use Http\Client\Common\Plugin;
use Http\Promise\FulfilledPromise;
use Http\Promise\Promise;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use SolidWorx\SimpleHttp\Enum\HttpMethod;

final class CachePlugin implements Plugin
{
    private CacheItemPoolInterface $cacheItemPool;
    private int $ttl;

    public function __construct(CacheItemPoolInterface $cacheItemPool, int $ttl = 0)
    {
        $this->cacheItemPool = $cacheItemPool;
        $this->ttl = $ttl;
    }

    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        // If the request method cannot be cached, then skip caching
        if (\in_array($request->getMethod(), array_map(static fn (HttpMethod $method): string => $method->value, HttpMethod::nonCachableMethods()), true)) {
            return $next($request);
        }

        $cacheKey = $this->getCacheKey($request);

        try {
            $cacheItem = $this->cacheItemPool->getItem($cacheKey);

            if ($cacheItem->isHit()) {
                return new FulfilledPromise($cacheItem->get());
            }
        } catch (InvalidArgumentException $e) {
        }

        $promise = $next($request);

        if (isset($cacheItem)) {
            $promise->then(function ($response) use ($cacheItem) {
                $cacheItem->set($response);

                if ($this->ttl > 0) {
                    $cacheItem->expiresAfter($this->ttl);
                }

                $this->cacheItemPool->save($cacheItem);
            });
        }

        return $promise;
    }

    private function getCacheKey(RequestInterface $request): string
    {
        return md5($request->getMethod().$request->getUri());
    }
}
