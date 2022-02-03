<?php

declare(strict_types=1);

/*
 * This file is part of SolidWorx/Apify project.
 *
 * Copyright (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SolidWorx\ApiFy\Tests;

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
use PHPUnit\Framework\TestCase;
use SolidWorx\ApiFy\Exception\MissingUrlException;
use SolidWorx\ApiFy\HttpClient;
use SolidWorx\ApiFy\RequestBuilder;
use function file_get_contents;

final class HttpClientTest extends TestCase
{
    public function testItCreatesAnInstanceWithABaseUrl(): void
    {
        $httpClient = HttpClient::create();

        $this->invoke($httpClient, function () {
            /* @var RequestBuilder $this */
            HttpClientTest::assertEmpty($this->url);
        });

        $httpClient = $httpClient->setBaseUri('https://foo.bar.com');

        $this->invoke($httpClient, function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertEquals(
                [
                    new BaseUriPlugin(
                        Psr17FactoryDiscovery::findUriFactory()->createUri('https://foo.bar.com')
                    )
                ],
                $this->plugins
            );
        });
    }

    public function testRequestWithBasicAuth(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->basicAuth('foo', 'bar')
            ->url('https://example.com');

        $this->invoke($httpClient, function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertEquals(
                [
                    new AuthenticationPlugin(new BasicAuth('foo', 'bar'))
                ],
                $this->plugins
            );
        });
    }

    public function testRequestWithBearerAuth(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->bearerToken('foobar')
            ->url('https://example.com');

        $this->invoke($httpClient, function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertEquals(
                [
                    new AuthenticationPlugin(new Bearer('foobar'))
                ],
                $this->plugins
            );
        });
    }

    public function testRequestWithUrl(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com/foo/bar/baz');

        $this->invoke($httpClient, function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertEquals(
                'https://example.com/foo/bar/baz',
                $this->url
            );
        });
    }

    public function testDisableSslVerification(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com')
            ->disableSslVerification();

        $this->invoke($httpClient, function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertFalse($this->options->verifyHost);
            HttpClientTest::assertFalse($this->options->verifyPeer);
        });
    }

    public function testWithJsonBody(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com')
            ->json(['foo' => 'bar']);

        $this->invoke($httpClient, function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertSame(['Content-Type' => 'application/json', 'Accept' => 'application/json'], $this->options->headers);
            HttpClientTest::assertSame('{"foo":"bar"}', $this->options->body);
        });
    }

    public function testWithFormDataBody(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com')
            ->formData(['foo' => 'bar', 'baz' => 'foobar']);

        $this->invoke($httpClient, function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertSame(['Content-Type' => 'application/x-www-form-urlencoded'], $this->options->headers);
            HttpClientTest::assertSame('foo=bar&baz=foobar', $this->options->body);
        });
    }

    public function testWithQueryParameters(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com')
            ->query(['foo' => 'bar', 'baz' => 'foobar']);


        $this->invoke($httpClient, function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertEquals(
                [
                    new QueryDefaultsPlugin(['foo' => 'bar', 'baz' => 'foobar'])
                ],
                $this->plugins
            );
        });
    }

    public function testWithHeaders(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com')
            ->header('X-API-TOKEN', 'ABC-DEF');

        $this->invoke($httpClient, function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertEquals(
                [
                    'X-API-TOKEN' => 'ABC-DEF',
                ],
                $this->options->headers
            );
        });

        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com')
            ->header('X-API-TOKEN', 'ABC-DEF')
            ->header('Accept', 'application/json');

        $this->invoke($httpClient, function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertEquals(
                [
                    'X-API-TOKEN' => 'ABC-DEF',
                    'Accept' => 'application/json',
                ],
                $this->options->headers
            );
        });
    }

    public function testWithRequestMethods(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com')
            ->method('put');

        $this->invoke($httpClient, function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertEquals('PUT', $this->method);
        });
    }

    public function testWithGetHelperMethods(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->get()
            ->url('https://example.com');

        $this->invoke($httpClient, function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertEquals('GET', $this->method);
        });
    }

    public function testWithPostHelperMethods(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->post()
            ->url('https://example.com');

        $this->invoke($httpClient, function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertEquals('POST', $this->method);
        });
    }

    public function testWithPutHelperMethods(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->put()
            ->url('https://example.com');

        $this->invoke($httpClient, function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertEquals('PUT', $this->method);
        });
    }

    public function testWithPatchHelperMethods(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->patch()
            ->url('https://example.com');

        $this->invoke($httpClient, function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertEquals('PATCH', $this->method);
        });
    }

    public function testWithOptionsHelperMethods(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->options()
            ->url('https://example.com');

        $this->invoke($httpClient, function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertEquals('OPTIONS', $this->method);
        });
    }

    public function testWithDeleteHelperMethods(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->delete()
            ->url('https://example.com');

        $this->invoke($httpClient, function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertEquals('DELETE', $this->method);
        });
    }

    public function testWithProgress(): void
    {
        $progressFunction = static function () {};

        $httpClient = HttpClient::create($this->getMockGuzzleClient(new Response()))
            ->url('https://example.com')
            ->header('Accept', 'application/json')
            ->progress($progressFunction);

        $this->invoke($httpClient, function () use ($progressFunction): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertSame($progressFunction, $this->options->onProgress);
        });
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
        try {
            $file = tempnam(sys_get_temp_dir(), 'api');
            assert(false !== $file);

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
        try {
            $file = tempnam(sys_get_temp_dir(), 'api');
            assert(false !== $file);
            file_put_contents($file, 'a b c 1 2 3');

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

        $this->invoke($httpClient, function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertSame(file_get_contents(__FILE__), $this->options->files['field']->getBody());
        });
    }

    public function testHttpVersion1(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com')
            ->httpVersion(HttpClient::HTTP_VERSION_1);

        $this->invoke($httpClient, function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertEquals('1.1', $this->options->httpVersion);
        });
    }

    public function testHttpVersion2(): void
    {
        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com')
            ->httpVersion(HttpClient::HTTP_VERSION_2);

        $this->invoke($httpClient, function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertEquals('2.0', $this->options->httpVersion);
        });

        $httpClient = HttpClient::create($this->getMockGuzzleClient())
            ->url('https://example.com')
            ->http2();

        $this->invoke($httpClient, function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertEquals('2.0', $this->options->httpVersion);
        });
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
        $getOptionsProperty = static function (RequestBuilder $object) {
            $ref = new \ReflectionProperty($object, 'options');
            $ref->setAccessible(true);

            return $ref->getValue($object);
        };

        self::assertNotSame($expected, $actual);
        self::assertNotSame($getOptionsProperty($expected), $getOptionsProperty($actual));
    }

    private function invoke(RequestBuilder $httpClient, callable $assert): void
    {
        $closure = Closure::bind($assert, $httpClient, $httpClient);

        if ($closure === false) {
            $this->fail('Closure could not be bound to RequestBuilder');
        }

        $closure();
    }

    private function getMockGuzzleClient(Response ...$response): Client
    {
        return new Client([
            'handler' => HandlerStack::create(new MockHandler($response))
        ]);
    }
}
