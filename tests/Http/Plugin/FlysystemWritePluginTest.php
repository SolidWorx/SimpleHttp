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

use Http\Client\Promise\HttpFulfilledPromise;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\FilesystemOperator;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use SolidWorx\SimpleHttp\Http\Plugin\FlysystemWritePlugin;

/**
 * @coversDefaultClass \SolidWorx\SimpleHttp\Http\Plugin\FlysystemWritePlugin
 */
final class FlysystemWritePluginTest extends TestCase
{
    /**
     * @param class-string<FilesystemInterface>|class-string<FilesystemOperator> $class
     *
     * @dataProvider flysystemProvider
     */
    public function testHandleRequestWithFlysytemV1(string $class): void
    {
        $path = \sys_get_temp_dir();
        $body = $this->createMock(StreamInterface::class);

        $resource = \fopen('php://temp', 'rb+');

        if (false === $resource) {
            self::fail('Could not open temp file');
        }

        $body->expects(self::exactly(2))
            ->method('detach')
            ->willReturn($resource);

        $filesystem = $this->createMock($class);
        $filesystem->expects(self::atLeastOnce())
            ->method('writeStream')
            ->with($path, $resource);

        $plugin = new FlysystemWritePlugin($filesystem, $path);

        $request = (new Request('GET', 'https://example.com/foo', ['Content-Type' => 'text/html']));

        $verify = static function (RequestInterface $request) use ($body): HttpFulfilledPromise {
            self::assertEquals('https://example.com/foo', $request->getUri()->__toString());

            return new HttpFulfilledPromise(new Response(200, ['Content-Type' => 'text/html'], $body));
        };

        try {
            $promise = $plugin->handleRequest($request, $verify, $verify);

            /** @var Response $response */
            $response = $promise->wait();

            self::assertSame(200, $response->getStatusCode());
            self::assertSame('text/html', $response->getHeaderLine('Content-Type'));
            self::assertSame($resource, $response->getBody()->detach());
            self::assertSame('1.1', $response->getProtocolVersion());
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        } finally {
            fclose($resource);
        }
    }

    /**
     * @param class-string<FilesystemInterface>|class-string<FilesystemOperator> $class
     *
     * @dataProvider flysystemProvider
     */
    public function testHandleRequestWithFlysytemV1AndSeekableStream(string $class): void
    {
        $path = \sys_get_temp_dir();
        $body = $this->createMock(StreamInterface::class);

        $resource = \fopen('php://temp', 'rb+');

        if (false === $resource) {
            self::fail('Could not open temp file');
        }

        \fwrite($resource, 'foo');
        \fseek($resource, 3);

        $body->expects(self::exactly(2))
            ->method('detach')
            ->willReturn($resource);

        $filesystem = $this->createMock($class);

        $filesystem->expects(self::once())
            ->method('writeStream')
            ->with($path, $resource);

        $filesystem->expects(self::once())
            ->method('readStream')
            ->with($path)
            ->willReturnCallback(static function () use ($resource) {
                rewind($resource);

                return $resource;
            });

        $plugin = new FlysystemWritePlugin($filesystem, $path);

        $request = (new Request('GET', 'https://example.com/foo', ['Content-Type' => 'text/html']));

        $verify = static function (RequestInterface $request) use ($body): HttpFulfilledPromise {
            self::assertEquals('https://example.com/foo', $request->getUri()->__toString());

            return new HttpFulfilledPromise(new Response(200, ['Content-Type' => 'text/html'], $body));
        };

        try {
            $promise = $plugin->handleRequest($request, $verify, $verify);

            /** @var Response $response */
            $response = $promise->wait();

            $stream = $body->detach();
            self::assertTrue(is_resource($stream));
            self::assertSame(0, \ftell($stream));

            self::assertSame(200, $response->getStatusCode());
            self::assertSame('text/html', $response->getHeaderLine('Content-Type'));
            self::assertSame($resource, $response->getBody()->detach());
            self::assertSame('1.1', $response->getProtocolVersion());
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        } finally {
            fclose($resource);
        }
    }

    /**
     * @param class-string<FilesystemInterface>|class-string<FilesystemOperator> $class
     *
     * @dataProvider flysystemProvider
     */
    public function testHandleRequestWithFlysytemV1AndInvalidResource(string $class): void
    {
        $path = \sys_get_temp_dir();
        $body = $this->createMock(StreamInterface::class);

        $body->expects(self::once())
            ->method('detach')
            ->willReturn('');

        $filesystem = $this->createMock($class);
        $filesystem->expects(self::never())
            ->method('writeStream');

        $plugin = new FlysystemWritePlugin($filesystem, $path);

        $request = (new Request('GET', 'https://example.com/foo', ['Content-Type' => 'text/html']));
        $response = new Response(200, ['Content-Type' => 'text/html'], $body);

        $verify = static function (RequestInterface $request) use ($response): HttpFulfilledPromise {
            self::assertEquals('https://example.com/foo', $request->getUri()->__toString());

            return new HttpFulfilledPromise($response);
        };

        $promise = $plugin->handleRequest($request, $verify, $verify);

        /** @var Response $newResponse */
        $newResponse = $promise->wait();

        self::assertSame($response, $newResponse);
        self::assertSame(200, $newResponse->getStatusCode());
        self::assertSame('text/html', $newResponse->getHeaderLine('Content-Type'));
        self::assertSame('1.1', $newResponse->getProtocolVersion());
    }

    public function flysystemProvider(): \Generator
    {
        if (\interface_exists(FilesystemInterface::class)) {
            yield [FilesystemInterface::class];
        }

        if (\interface_exists(FilesystemOperator::class)) {
            yield [FilesystemOperator::class];
        }
    }
}
