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

use Exception;
use function fopen;
use function fseek;
use function ftell;
use function fwrite;
use Generator;
use Http\Client\Promise\HttpFulfilledPromise;
use function interface_exists;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\FilesystemWriter;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use SolidWorx\SimpleHttp\Http\Plugin\FlysystemWritePlugin;
use function sys_get_temp_dir;

/**
 * @coversDefaultClass \SolidWorx\SimpleHttp\Http\Plugin\FlysystemWritePlugin
 */
final class FlysystemWritePluginTest extends TestCase
{
    /**
     * @param (FilesystemInterface|FilesystemWriter)&MockObject $filesystem
     *
     * @dataProvider flysystemProvider
     */
    public function testHandleRequestWithFlysytemV1($filesystem): void
    {
        $path = sys_get_temp_dir();
        $body = $this->createMock(StreamInterface::class);

        $resource = fopen('php://temp', 'rb+');

        if (false === $resource) {
            self::fail('Could not open temp file');
        }

        $body->expects(self::once())
            ->method('detach')
            ->willReturn($resource);

        $filesystem->expects(self::once())
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
        } catch (Exception $e) {
            self::fail($e->getMessage());
        } finally {
            fclose($resource);
        }
    }

    /**
     * @param (FilesystemInterface|FilesystemWriter)&MockObject $filesystem
     *
     * @dataProvider flysystemProvider
     */
    public function testHandleRequestWithFlysytemV1AndSeekableStream($filesystem): void
    {
        $path = sys_get_temp_dir();
        $body = $this->createMock(StreamInterface::class);

        $resource = fopen('php://temp', 'rb+');

        if (false === $resource) {
            self::fail('Could not open temp file');
        }

        fwrite($resource, 'foo');
        fseek($resource, 3);

        $body->expects(self::once())
            ->method('detach')
            ->willReturn($resource);

        $body->expects(self::once())
            ->method('isSeekable')
            ->willReturn(true);

        $filesystem->expects(self::once())
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

            self::assertSame(0, ftell($resource));

            self::assertSame(200, $response->getStatusCode());
            self::assertSame('text/html', $response->getHeaderLine('Content-Type'));
            self::assertSame($resource, $response->getBody()->detach());
            self::assertSame('1.1', $response->getProtocolVersion());
        } catch (Exception $e) {
            self::fail($e->getMessage());
        } finally {
            fclose($resource);
        }
    }

    /**
     * @param (FilesystemInterface|FilesystemWriter)&MockObject $filesystem
     *
     * @dataProvider flysystemProvider
     */
    public function testHandleRequestWithFlysytemV1AndInvalidResource($filesystem): void
    {
        $path = sys_get_temp_dir();
        $body = $this->createMock(StreamInterface::class);

        $body->expects(self::once())
            ->method('detach')
            ->willReturn('');

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

    public function flysystemProvider(): Generator
    {
        if (interface_exists(FilesystemInterface::class)) {
            yield [$this->createMock(FilesystemInterface::class)];
        }

        if (interface_exists(FilesystemWriter::class)) {
            yield [$this->createMock(FilesystemWriter::class)];
        }
    }
}
