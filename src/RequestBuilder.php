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

namespace SolidWorx\SimpleHttp;

use Http\Client\Common\PluginClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use SolidWorx\SimpleHttp\Discovery\HttpAsyncClientDiscovery;
use SolidWorx\SimpleHttp\Exception\MissingUrlException;
use SolidWorx\SimpleHttp\Traits\HttpMethodsTrait;
use SolidWorx\SimpleHttp\Traits\HttpOptionsTrait;
use Throwable;
use function in_array;

final class RequestBuilder
{
    use HttpOptionsTrait;
    use HttpMethodsTrait;

    private ?UriInterface $url = null;
    private RequestOptions $options;
    private ?ClientInterface $client = null;

    public function __construct(?ClientInterface $client = null)
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
        $request->url = Psr17FactoryDiscovery::findUriFactory()->createUri($url);

        return $request;
    }

    /**
     * @throws Throwable
     */
    public function request(): Response
    {
        $client = HttpAsyncClientDiscovery::find($this->options, $this->client);

        $pluginClient = new PluginClient($client, $this->plugins);

        return new Response($pluginClient->sendAsyncRequest($this->build()));
    }

    private function build(): RequestInterface
    {
        if (null === $this->url) {
            throw new MissingUrlException();
        }

        $request = Psr17FactoryDiscovery::findRequestFactory()->createRequest(
            $this->method,
            $this->url
        )
            ->withProtocolVersion($this->options->httpVersion);

        $body = $this->options->getBody();

        if ('' !== $body) {
            if (in_array($this->method, [HttpClient::METHOD_GET, HttpClient::METHOD_HEAD], true)) {
                // When a body is set, but the request is a method that does not require a body, then we default to a POST request
                $request = $request->withMethod(HttpClient::METHOD_POST);
            }

            $streamFactory = Psr17FactoryDiscovery::findStreamFactory();
            $request = $request->withBody($streamFactory->createStream($this->options->getBody()));
        }

        /** @var string $value */
        foreach ($this->options->headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $request;
    }
}
