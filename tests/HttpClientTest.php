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
use PHPUnit\Framework\TestCase;
use SolidWorx\ApiFy\Exception\InvalidArgumentException;
use SolidWorx\ApiFy\Exception\MissingUrlException;
use SolidWorx\ApiFy\HttpClient;
use SolidWorx\ApiFy\Progress;
use SolidWorx\ApiFy\RequestBuilder;
use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpClient\ScopingHttpClient;

final class HttpClientTest extends TestCase
{
    public function testItCreatesAnInstanceWithABaseUrl(): void
    {
        $httpClient = HttpClient::create();

        Closure::bind(function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertNotInstanceOf(ScopingHttpClient::class, $this->client);
        }, $httpClient, $httpClient)();

        $httpClient = $httpClient->setBaseUri('https://foo.bar.com');

        Closure::bind(function (): void {
            /* @var RequestBuilder $this */
            HttpClientTest::assertInstanceOf(ScopingHttpClient::class, $this->client);

            Closure::bind(function (): void {
                /* @var ScopingHttpClient $this */
                HttpClientTest::assertSame(['https\://foo\.bar\.com/' => ['base_uri' => 'https://foo.bar.com']], $this->defaultOptionsByRegexp);
            }, $this->client, $this->client)();
        }, $httpClient, $httpClient)();
    }

    public function testRequestWithBasicAuth(): void
    {
        $mockResponse = new MockResponse();
        $httpClient = HttpClient::create(new MockHttpClient($mockResponse));

        $httpClient->basicAuth('foo', 'bar')
            ->url('https://example.com')
            ->request();

        self::assertSame(['Accept: */*', 'Authorization: Basic Zm9vOmJhcg=='], $mockResponse->getRequestOptions()['headers']);
    }

    public function testRequestWithBearerAuth(): void
    {
        $mockResponse = new MockResponse();
        $httpClient = HttpClient::create(new MockHttpClient($mockResponse));

        $httpClient->bearerToken('foobar')
            ->url('https://example.com')
            ->request();

        self::assertSame(['Accept: */*', 'Authorization: Bearer foobar'], $mockResponse->getRequestOptions()['headers']);
    }

    public function testRequestWithUrl(): void
    {
        $mockResponse = new MockResponse();
        $httpClient = HttpClient::create(new MockHttpClient($mockResponse));

        $httpClient->url('https://example.com/foo/bar/baz')
            ->request();

        self::assertSame('https://example.com/foo/bar/baz', $mockResponse->getRequestUrl());
    }

    public function testDisableSslVerification(): void
    {
        $mockResponse = new MockResponse();
        $httpClient = HttpClient::create(new MockHttpClient($mockResponse));

        $httpClient->url('https://example.com')
            ->disableSslVerification()
            ->request();

        $requestOptions = $mockResponse->getRequestOptions();
        self::assertFalse($requestOptions['verify_peer'] ?? null);
        self::assertFalse($requestOptions['verify_host'] ?? null);
    }

    public function testWithBaseUrl(): void
    {
        $mockResponse = new MockResponse();
        $httpClient = HttpClient::create(new MockHttpClient($mockResponse))
            ->setBaseUri('https://foo.bar');

        $httpClient->url('/api/path')
            ->request();

        self::assertSame('https://foo.bar/api/path', $mockResponse->getRequestUrl());
    }

    public function testWithJsonBody(): void
    {
        $mockResponse = new MockResponse();
        $httpClient = HttpClient::create(new MockHttpClient($mockResponse));

        $httpClient->url('https://example.com')
            ->json(['foo' => 'bar'])
            ->request();

        $requestOptions = $mockResponse->getRequestOptions();
        self::assertSame(['Content-Type: application/json', 'Accept: */*'], $requestOptions['headers'] ?? []);
        self::assertSame('{"foo":"bar"}', $requestOptions['body'] ?? '');
    }

    public function testWithFormDataBody(): void
    {
        $mockResponse = new MockResponse();
        $httpClient = HttpClient::create(new MockHttpClient($mockResponse));

        $httpClient->url('https://example.com')
            ->formData(['foo' => 'bar', 'baz' => 'foobar'])
            ->request();

        $requestOptions = $mockResponse->getRequestOptions();
        self::assertSame('foo=bar&baz=foobar', $requestOptions['body'] ?? '');
    }

    public function testWithQueryParameters(): void
    {
        $mockResponse = new MockResponse();
        $httpClient = HttpClient::create(new MockHttpClient($mockResponse));

        $httpClient->url('https://example.com')
            ->query(['foo' => 'bar', 'baz' => 'foobar'])
            ->request();

        $requestOptions = $mockResponse->getRequestOptions();
        self::assertSame('https://example.com/?foo=bar&baz=foobar', $mockResponse->getRequestUrl());
        self::assertSame(['foo' => 'bar', 'baz' => 'foobar'], $requestOptions['query'] ?? '');
    }

    public function testWithHeaders(): void
    {
        $mockResponse = new MockResponse();
        $httpClient = HttpClient::create(new MockHttpClient($mockResponse));

        $httpClient->url('https://example.com')
            ->header('X-API-TOKEN', 'ABC-DEF')
            ->request();

        $requestOptions = $mockResponse->getRequestOptions();
        self::assertSame(['X-API-TOKEN: ABC-DEF', 'Accept: */*'], $requestOptions['headers'] ?? []);

        $mockResponse = new MockResponse();
        $httpClient = HttpClient::create(new MockHttpClient($mockResponse));
        $httpClient->url('https://example.com')
            ->header('Accept', 'application/json')
            ->request();

        $requestOptions = $mockResponse->getRequestOptions();
        self::assertSame(['Accept: application/json'], $requestOptions['headers'] ?? []);
    }

    public function testOverwriteHeaders(): void
    {
        $mockResponse = new MockResponse();
        $httpClient = HttpClient::create(new MockHttpClient($mockResponse));
        $httpClient->url('https://example.com')
            ->header('Accept', 'application/json')
            ->request();

        $requestOptions = $mockResponse->getRequestOptions();
        self::assertSame(['Accept: application/json'], $requestOptions['headers'] ?? []);
    }

    public function testWithRequestMethods(): void
    {
        $mockResponse = new MockResponse();
        $httpClient = HttpClient::create(new MockHttpClient($mockResponse));
        $httpClient->url('https://example.com')
            ->method('put')
            ->request();

        self::assertSame('PUT', $mockResponse->getRequestMethod());
    }

    public function testWithGetHelperMethods(): void
    {
        $mockResponse = new MockResponse();
        $httpClient = HttpClient::create(new MockHttpClient($mockResponse));
        $httpClient->get()
            ->url('https://example.com')
            ->request();

        self::assertSame('GET', $mockResponse->getRequestMethod());
    }

    public function testWithPostHelperMethods(): void
    {
        $mockResponse = new MockResponse();
        $httpClient = HttpClient::create(new MockHttpClient($mockResponse));
        $httpClient->post()
            ->url('https://example.com')
            ->request();

        self::assertSame('POST', $mockResponse->getRequestMethod());
    }

    public function testWithProgress(): void
    {
        $progressCalled = false;
        $mockResponse = new MockResponse();
        $httpClient = HttpClient::create(new MockHttpClient($mockResponse));
        $httpClient->url('https://example.com')
            ->header('Accept', 'application/json')
            ->progress(static function (Progress $progress) use (&$progressCalled): void {
                self::assertSame(0, $progress->getDownloaded());
                self::assertSame(0, $progress->getTotalSize());
                $info = $progress->getInfo();

                self::assertContains($info['http_code'] ?? null, [0, 200]);

                unset($info['start_time'], $info['http_code']);

                self::assertEquals(['response_headers' => [], 'error' => null, 'canceled' => false, 'redirect_count' => 0, 'redirect_url' => null, 'http_method' => 'GET', 'user_data' => null, 'url' => 'https://example.com/'], $info);

                $progressCalled = true;
            })
            ->request();

        self::assertTrue($progressCalled);
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

            $mockResponse = new MockResponse('foo bar baz');
            HttpClient::create(new MockHttpClient($mockResponse))
                ->url('https://example.com')
                ->streamToFile($file)
                ->request()
                ->getContent();

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

            $mockResponse = new MockResponse('foo bar baz');
            $httpClient = HttpClient::create(new MockHttpClient($mockResponse));

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
        try {
            $file = tempnam(sys_get_temp_dir(), 'api');
            assert(false !== $file);
            file_put_contents($file, 'a b c 1 2 3');

            $mockResponse = new MockResponse('', ['size_upload' => 0.0]);
            $httpClient = HttpClient::create(new MockHttpClient($mockResponse));
            $response = $httpClient
                ->url('https://example.com')
                ->uploadFile('field', $file)
                ->request();

            $headers = $mockResponse->getRequestOptions()['headers'];
            self::assertNotEmpty($headers);
            self::assertStringStartsWith('content-type: multipart/form-data; boundary=', $headers[0]);
            self::assertSame('Accept: */*', $headers[1]);
            $body = $mockResponse->getRequestOptions()['body'];
            self::assertInstanceOf(Closure::class, $body);
            self::assertSame(182.0, $response ->getInfo()['size_upload']);
        } finally {
            unlink($file);
        }
    }

    public function testUploadFileWithFormData(): void
    {
        try {
            $file = tempnam(sys_get_temp_dir(), 'api');
            assert(false !== $file);
            file_put_contents($file, 'a b c 1 2 3');

            $mockResponse = new MockResponse('', ['size_upload' => 0.0]);
            $httpClient = HttpClient::create(new MockHttpClient($mockResponse));
            $response = $httpClient
                ->url('https://example.com')
                ->formData(['foo' => 'bar'])
                ->uploadFile('field', $file)
                ->request();

            $headers = $mockResponse->getRequestOptions()['headers'];
            self::assertNotEmpty($headers);
            self::assertStringStartsWith('content-type: multipart/form-data; boundary=', $headers[0]);
            self::assertSame('Accept: */*', $headers[1]);
            $body = $mockResponse->getRequestOptions()['body'];
            self::assertInstanceOf(Closure::class, $body);
            self::assertSame(319.0, $response ->getInfo()['size_upload']);
        } finally {
            unlink($file);
        }
    }

    public function testUploadFileWithStringBody(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('File uploads cannot be used without an array body');

        HttpClient::create(new MockHttpClient())
            ->url('https://example.com')
            ->body('foo')
            ->uploadFile('field', __FILE__)
            ->request();
    }

    public function testUploadFileWithJson(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('File uploads cannot be used without an array body');

        HttpClient::create(new MockHttpClient())
            ->url('https://example.com')
            ->json(['foo' => 'bar'])
            ->uploadFile('field', __FILE__)
            ->request();
    }

    public function testResponseInformation(): void
    {
        $mockResponse = new MockResponse();
        $httpClient = HttpClient::create(new MockHttpClient($mockResponse));
        $response = $httpClient
            ->url('http://example.com')
            ->request();

        self::assertFalse($response->isCanceled());
        self::assertNull($response->getError());
        self::assertNull($response->getError());
        self::assertSame(200, $response->getStatusCode());
        self::assertSame([], $response->getHeaders());
        self::assertSame('', $response->getContent());
        self::assertSame(200, $response->getHttpCode());
        self::assertSame('GET', $response->getHttpMethod());
        self::assertSame(0, $response->getRedirectCount());
        self::assertNull($response->getRedirectUrl());
        self::assertSame([], $response->getResponseHeaders());
        self::assertGreaterThan(0, $response->getStartTime());
        self::assertSame('http://example.com/', $response->getUrl());
        self::assertNull($response->getUserData());
        self::assertNotEmpty($response->getInfo());

        try {
            $response->toArray(false);
            self::fail('JsonException was not thrown');
        } catch (JsonException $e) {
            self::assertSame('Response body is empty.', $e->getMessage());
        }

        $response->cancel();
        self::assertTrue($response->isCanceled());
        self::assertSame('Response has been canceled.', $response->getError());
    }

    public function testResponseError(): void
    {
        $mockResponse = new MockResponse('[]', ['http_code' => 500]);
        $httpClient = HttpClient::create(new MockHttpClient($mockResponse));
        $response = $httpClient
            ->url('http://example.com')
            ->request();

        self::assertSame(500, $response->getStatusCode());

        self::assertSame([], $response->getHeaders(false));
        try {
            $response->getHeaders();
            self::fail('Exception not thrown for $response->getHeaders()');
        } catch (ServerException $e) {
            self::assertSame('HTTP 500 returned for "http://example.com/".', $e->getMessage());
        }

        self::assertSame('[]', $response->getContent(false));
        try {
            $response->getContent();
            self::fail('Exception not thrown for $response->getContent()');
        } catch (ServerException $e) {
            self::assertSame('HTTP 500 returned for "http://example.com/".', $e->getMessage());
        }

        self::assertEmpty($response->toArray(false));
        try {
            $response->toArray();
            self::fail('Exception not thrown for $response->toArray()');
        } catch (ServerException $e) {
            self::assertSame('HTTP 500 returned for "http://example.com/".', $e->getMessage());
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
        self::assertObjectIsNotTheSame($httpClient, $httpClient->streamToFile(''));
        self::assertObjectIsNotTheSame($httpClient, $httpClient->appendToFile(''));
        self::assertObjectIsNotTheSame($httpClient, $httpClient->uploadFile('', __FILE__));
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
}
