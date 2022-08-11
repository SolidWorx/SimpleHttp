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
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Promise\Promise;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\FilesystemOperator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

use function is_resource;

final class FlysystemWritePlugin implements Plugin
{
    /**
     * @var FilesystemInterface|FilesystemOperator
     */
    private $filesystem;
    private string $path;

    /**
     * @param FilesystemInterface|FilesystemOperator $filesystem
     */
    public function __construct($filesystem, string $path)
    {
        $this->filesystem = $filesystem;
        $this->path = $path;
    }

    /**
     * @throws FileNotFoundException
     * @throws Throwable
     * @throws FileExistsException
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        $responseFactory = Psr17FactoryDiscovery::findResponseFactory();
        $streamFactory = Psr17FactoryDiscovery::findStreamFactory();

        return $next($request)->then(function (ResponseInterface $response) use ($responseFactory, $streamFactory) {
            $body = $response->getBody();
            $stream = $body->detach();

            if (!is_resource($stream)) {
                return $response;
            }

            $this->filesystem->writeStream($this->path, $stream);

            $nextResponse = $responseFactory->createResponse(
                $response->getStatusCode(),
                $response->getReasonPhrase(),
            );

            foreach ($response->getHeaders() as $name => $value) {
                $nextResponse = $nextResponse->withHeader($name, $value);
            }

            $resource = $this->filesystem->readStream($this->path);

            if (is_resource($resource)) {
                $body = $streamFactory->createStreamFromResource($resource);
            }

            return $nextResponse
                ->withBody($body)
                ->withProtocolVersion($response->getProtocolVersion());
        });
    }
}
