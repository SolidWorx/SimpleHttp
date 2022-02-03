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

namespace SolidWorx\SimpleHttp\Tests;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Http\Client\Common\Plugin\AuthenticationPlugin;
use Http\Client\Common\Plugin\BaseUriPlugin;
use Http\Client\Common\Plugin\QueryDefaultsPlugin;
use Http\Client\Exception\HttpException;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Message\Authentication\BasicAuth;
use Http\Message\Authentication\Bearer;
use JsonException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use SolidWorx\SimpleHttp\Exception\MissingUrlException;
use SolidWorx\SimpleHttp\HttpClient;
use SolidWorx\SimpleHttp\RequestBuilder;
use SolidWorx\SimpleHttp\RequestOptions;
use function file_exists;
use function file_get_contents;

final class HttpClientTest extends TestCase
{
    public function testItCreatesAnInstanceWithABaseUrl(): void
    {
        $httpClient = HttpClient::create();

        $this->invoke(Closure::bind(function () {
            Assert::assertEmpty($this->url);
        }, $httpClient, $httpClient));

        $httpClient = $httpClient->setBaseUri('https://foo.bar.com');

        $this->invoke(Closure::bind(function (): void {
            Assert::assertEquals(
                [
                    new BaseUriPlugin(
                        Psr17FactoryDiscovery::findUriFactory()->createUri('https://foo.bar.com')
                    )
                ],
                $this->plugins
            );
        }, $httpClient, $httpClient));
    }

    public function testRequestWithBasicAuth(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->basicAuth('foo', 'bar')
            ->url('https://example.com');

        $this->invoke(Closure::bind(function (): void {
            Assert::assertEquals(
                [
                    new AuthenticationPlugin(new BasicAuth('foo', 'bar'))
                ],
                $this->plugins
            );
        }, $httpClient, $httpClient));
    }

    public function testRequestWithBearerAuth(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->bearerToken('foobar')
            ->url('https://example.com');

        $this->invoke(Closure::bind(function (): void {
            Assert::assertEquals(
                [
                    new AuthenticationPlugin(new Bearer('foobar'))
                ],
                $this->plugins
            );
        }, $httpClient, $httpClient));
    }

    public function testRequestWithUrl(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com/foo/bar/baz');

        $this->invoke(Closure::bind(function (): void {
            Assert::assertEquals(
                'https://example.com/foo/bar/baz',
                $this->url
            );
        }, $httpClient, $httpClient));
    }

    public function testDisableSslVerification(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com')
            ->disableSslVerification();

        $this->invoke(Closure::bind(function (): void {
            Assert::assertFalse($this->options->verifyHost);
            Assert::assertFalse($this->options->verifyPeer);
        }, $httpClient, $httpClient));
    }

    public function testWithJsonBody(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com')
            ->json(['foo' => 'bar']);

        $this->invoke(Closure::bind(function (): void {
            Assert::assertSame(['Content-Type' => 'application/json', 'Accept' => 'application/json'], $this->options->headers);
            Assert::assertSame('{"foo":"bar"}', $this->options->body);
        }, $httpClient, $httpClient));
    }

    public function testWithFormDataBody(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com')
            ->formData(['foo' => 'bar', 'baz' => 'foobar']);

        $this->invoke(Closure::bind(function (): void {
            Assert::assertSame(['Content-Type' => 'application/x-www-form-urlencoded'], $this->options->headers);
            Assert::assertSame('foo=bar&baz=foobar', $this->options->body);
        }, $httpClient, $httpClient));
    }

    public function testWithQueryParameters(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com')
            ->query(['foo' => 'bar', 'baz' => 'foobar']);


        $this->invoke(Closure::bind(function (): void {
            Assert::assertEquals(
                [
                    new QueryDefaultsPlugin(['foo' => 'bar', 'baz' => 'foobar'])
                ],
                $this->plugins
            );
        }, $httpClient, $httpClient));
    }

    public function testWithHeaders(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com')
            ->header('X-API-TOKEN', 'ABC-DEF');

        $this->invoke(Closure::bind(function (): void {
            Assert::assertEquals(
                [
                    'X-API-TOKEN' => 'ABC-DEF',
                ],
                $this->options->headers
            );
        }, $httpClient, $httpClient));

        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com')
            ->header('X-API-TOKEN', 'ABC-DEF')
            ->header('Accept', 'application/json');

        $this->invoke(Closure::bind(function (): void {
            Assert::assertEquals(
                [
                    'X-API-TOKEN' => 'ABC-DEF',
                    'Accept' => 'application/json',
                ],
                $this->options->headers
            );
        }, $httpClient, $httpClient));
    }

    public function testWithRequestMethods(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com')
            ->method('put');

        $this->invoke(Closure::bind(function (): void {
            /* @var RequestBuilder $this */
            Assert::assertEquals('PUT', $this->method);
        }, $httpClient, $httpClient));
    }

    public function testWithGetHelperMethods(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->get()
            ->url('https://example.com');

        $this->invoke(Closure::bind(function (): void {
            Assert::assertEquals('GET', $this->method);
        }, $httpClient, $httpClient));
    }

    public function testWithPostHelperMethods(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->post()
            ->url('https://example.com');

        $this->invoke(Closure::bind(function (): void {
            Assert::assertEquals('POST', $this->method);
        }, $httpClient, $httpClient));
    }

    public function testWithPutHelperMethods(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->put()
            ->url('https://example.com');

        $this->invoke(Closure::bind(function (): void {
            Assert::assertEquals('PUT', $this->method);
        }, $httpClient, $httpClient));
    }

    public function testWithPatchHelperMethods(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->patch()
            ->url('https://example.com');

        $this->invoke(Closure::bind(function (): void {
            Assert::assertEquals('PATCH', $this->method);
        }, $httpClient, $httpClient));
    }

    public function testWithOptionsHelperMethods(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->options()
            ->url('https://example.com');

        $this->invoke(Closure::bind(function (): void {
            Assert::assertEquals('OPTIONS', $this->method);
        }, $httpClient, $httpClient));
    }

    public function testWithDeleteHelperMethods(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->delete()
            ->url('https://example.com');

        $this->invoke(Closure::bind(function (): void {
            Assert::assertEquals('DELETE', $this->method);
        }, $httpClient, $httpClient));
    }

    public function testWithProgress(): void
    {
        $progressFunction = static function (): void {};

        $httpClient = HttpClient::create($this->getMockGuzzleClient(new Response()))
            ->url('https://example.com')
            ->header('Accept', 'application/json')
            ->progress($progressFunction);

        $this->invoke(Closure::bind(function () use ($progressFunction): void {
            Assert::assertSame($progressFunction, $this->options->onProgress);
        }, $httpClient, $httpClient));
    }

    public function testItThrowsExceptionWithAMissingUrl(): void
    {
        $this->expectException(MissingUrlException::class);
        $this->expectExceptionMessage('The "url" option is missing. Ensure you set the URL with the `$httpClient->url()` method.');
        $this->expectExceptionCode(0);

        $httpClient = HttpClient::create();
        $httpClient->request();
    }

    public function testItCanStreamToAFile(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'api');

        if (false === $file) {
            self::fail('Could not create temporary file');
        }

        try {
            HttpClient::create($this->getMockGuzzleClient(new Response(200, [], 'foo bar baz')))
                ->url('https://example.com')
                ->saveToFile($file)
                ->request();

            self::assertFileExists($file);
            self::assertSame('foo bar baz', file_get_contents($file));
        } finally {
            unlink($file);
        }
    }

    public function testItCanAppendToAFile(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'api');

        if (false === $file) {
            self::fail('Could not create temporary file');
        }

        file_put_contents($file, 'a b c 1 2 3');

        try {

            $httpClient = HttpClient::create($this->getMockGuzzleClient(new Response(200, [], 'foo bar baz')));

            $httpClient->url('https://example.com')
                ->appendToFile($file)
                ->request()
                ->getContent();

            self::assertFileExists($file);
            self::assertSame('a b c 1 2 3foo bar baz', file_get_contents($file));
        } finally {
            unlink($file);
        }
    }

    public function testUploadFile(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com')
            ->uploadFile('field', __FILE__);

        $this->invoke(Closure::bind(function (): void {
            Assert::assertStringEqualsFile(__FILE__, $this->options->files['field']->getBody());
        }, $httpClient, $httpClient));
    }

    public function testHttpVersion1(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com')
            ->httpVersion(HttpClient::HTTP_VERSION_1);

        $this->invoke(Closure::bind(function (): void {
            Assert::assertEquals('1.1', $this->options->httpVersion);
        }, $httpClient, $httpClient));
    }

    public function testHttpVersion2(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com')
            ->httpVersion(HttpClient::HTTP_VERSION_2);

        $this->invoke(Closure::bind(function (): void {
            Assert::assertEquals('2.0', $this->options->httpVersion);
        }, $httpClient, $httpClient));

        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com')
            ->http2();

        $this->invoke(Closure::bind(function (): void {
            Assert::assertEquals('2.0', $this->options->httpVersion);
        }, $httpClient, $httpClient));
    }

    public function testResponseInformation(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient(new Response()));
        $response = $httpClient
            ->url('http://example.com')
            ->request();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame([], $response->getHeaders());
        self::assertSame('', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertSame([], $response->getHeaders());

        try {
            $response->toArray();
            self::fail('JsonException was not thrown');
        } catch (JsonException $e) {
            self::assertSame('Syntax error', $e->getMessage());
        }
    }

    public function testResponseError(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient(new Response(500)));
        try {
            $httpClient
                ->url('http://example.com')
                ->request();
        } catch (HttpException $e) {
            self::assertSame(500, $e->getCode());
            self::assertSame('Server error: `GET http://example.com` resulted in a `500 Internal Server Error` response', $e->getMessage());
            self::assertSame(500, $e->getResponse()->getStatusCode());
            self::assertSame('Internal Server Error', $e->getResponse()->getReasonPhrase());
            self::assertSame([], $e->getResponse()->getHeaders());
            self::assertSame('', (string) $e->getResponse()->getBody());
        }
    }

    public function testImmutability(): void
    {
        $httpClient = HttpClient::create();

        self::assertObjectIsNotTheSame($httpClient, $httpClient->url('foo'));
        self::assertObjectIsNotTheSame($httpClient, $httpClient->method('POST'));
        self::assertObjectIsNotTheSame($httpClient, $httpClient->progress(static function (): void {}));
        self::assertObjectIsNotTheSame($httpClient, $httpClient->header('foo', 'bar'));
        self::assertObjectIsNotTheSame($httpClient, $httpClient->formData([]));
        self::assertObjectIsNotTheSame($httpClient, $httpClient->json([]));
        self::assertObjectIsNotTheSame($httpClient, $httpClient->setBaseUri('http://example.com'));
        self::assertObjectIsNotTheSame($httpClient, $httpClient->disableSslVerification());
        self::assertObjectIsNotTheSame($httpClient, $httpClient->basicAuth('foo'));
        self::assertObjectIsNotTheSame($httpClient, $httpClient->bearerToken('foo'));
        self::assertObjectIsNotTheSame($httpClient, $httpClient->body(''));
        self::assertObjectIsNotTheSame($httpClient, $httpClient->get());
        self::assertObjectIsNotTheSame($httpClient, $httpClient->post());
        self::assertObjectIsNotTheSame($httpClient, $httpClient->disableSslVerification());
        self::assertObjectIsNotTheSame($httpClient, $httpClient->query());
        self::assertObjectIsNotTheSame($httpClient, $httpClient->saveToFile(__FILE__));
        self::assertObjectIsNotTheSame($httpClient, $httpClient->appendToFile(__FILE__));
        self::assertObjectIsNotTheSame($httpClient, $httpClient->uploadFile('', __FILE__));
        self::assertObjectIsNotTheSame($httpClient, $httpClient->httpVersion(''));
    }

    private static function assertObjectIsNotTheSame(RequestBuilder $expected, RequestBuilder $actual): void
    {
        /** @return mixed */
        $getOptionsProperty = static function (RequestBuilder $object) {
            $ref = new \ReflectionProperty($object, 'options');
            $ref->setAccessible(true);

            return $ref->getValue($object);
        };

        self::assertNotSame($expected, $actual);
        self::assertNotSame($getOptionsProperty($expected), $getOptionsProperty($actual));
    }

    /**
     * @param Closure|false $closure
     */
    private function invoke($closure): void
    {
        if ($closure === false) {
            self::fail('Closure could not be bound to RequestBuilder');
        }

        $closure();
    }

    private function getMockGuzzleClient(Response ...$response): Client
    {
        /** @var array<int, mixed> $response */
        return new Client([
            'handler' => HandlerStack::create(new MockHandler($response))
        ]);
    }
}
