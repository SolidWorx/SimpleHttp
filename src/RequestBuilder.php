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

namespace SolidWorx\ApiFy;

use SolidWorx\ApiFy\Exception\MissingUrlException;
use SolidWorx\ApiFy\Traits\HttpOptionsTrait;
use Symfony\Component\HttpClient\ScopingHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class RequestBuilder
{
    use HttpOptionsTrait;

    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';

    private string $url = '';
    private string $method = self::METHOD_GET;

    private RequestOptions $options;

    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->options = new RequestOptions();
        $this->client = $client;
    }

    public function __clone()
    {
        $this->options = clone $this->options;
    }

    public function url(string $url): self
    {
        $request = clone $this;
        $request->url = $url;

        return $request;
    }

    public function get(): RequestBuilder
    {
        return $this->method(self::METHOD_GET);
    }

    public function post(): RequestBuilder
    {
        return $this->method(self::METHOD_POST);
    }

    public function method(string $method): self
    {
        $request = clone $this;
        $request->method = \strtoupper($method);

        return $request;
    }

    private function build(): array
    {
        if ('' === $this->url) {
            throw new MissingUrlException();
        }

        return [
            $this->method,
            $this->url,
            $this->options->build(),
        ];
    }

    public function request(): Response
    {
        return new Response($this->client->request(...$this->build()));
    }

    public function setBaseUri(string $url): self
    {
        $builder = clone $this;
        $builder->client = ScopingHttpClient::forBaseUri($builder->client, $url);

        return $builder;
    }
}
