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

namespace SolidWorx\SimpleHttp\Traits;

use Closure;
use function fopen;
use Http\Client\Common\Plugin;
use Http\Client\Common\Plugin\BaseUriPlugin;
use Http\Client\Common\Plugin\QueryDefaultsPlugin;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Message\Authentication\BasicAuth;
use Http\Message\Authentication\Bearer;
use SolidWorx\SimpleHttp\Exception\InvalidArgumentException;
use SolidWorx\SimpleHttp\HttpClient;
use SolidWorx\SimpleHttp\Progress;
use Symfony\Component\Mime\Part\DataPart;
use Traversable;

trait HttpOptionsTrait
{
    /**
     * @var Plugin[]
     */
    private array $plugins = [];

    /**
     * @return $this
     */
    public function disableSslVerification(): self
    {
        $httpClient = clone $this;
        $httpClient->options = $httpClient->options->verifyHost(false)
            ->verifyPeer(false);

        return $httpClient;
    }

    /**
     * @return $this
     */
    public function basicAuth(string $username, ?string $password = null): self
    {
        $httpClient = clone $this;

        $httpClient->plugins[] = new Plugin\AuthenticationPlugin(new BasicAuth($username, (string) $password));

        return $httpClient;
    }

    /**
     * @return $this
     */
    public function bearerToken(string $token): self
    {
        $httpClient = clone $this;
        $httpClient->plugins[] = new Plugin\AuthenticationPlugin(new Bearer($token));

        return $httpClient;
    }

    /**
     * @param array|string|resource|Traversable|Closure $body
     *
     * @return $this
     */
    public function body($body): self
    {
        $httpClient = clone $this;

        $httpClient->options = $httpClient->options->body($body);

        return $httpClient;
    }

    /**
     * @return $this
     */
    public function query(array $queryParams = []): self
    {
        $httpClient = clone $this;
        $httpClient->plugins[] = new QueryDefaultsPlugin($queryParams);

        return $httpClient;
    }

    /**
     * @return $this
     */
    public function header(string $key, ?string $value = null): self
    {
        $httpClient = clone $this;
        $httpClient->options = $httpClient->options->addHeader($key, $value);

        return $httpClient;
    }

    /**
     * @return $this
     */
    public function formData(array $body): self
    {
        return $this->body($body);
    }

    /**
     * @return $this
     */
    public function json(array $json): self
    {
        $httpClient = clone $this;

        return $httpClient->header('Content-Type', 'application/json')
            ->header('Accept', 'application/json')
            ->body(\json_encode($json, JSON_THROW_ON_ERROR));
    }

    /**
     * @param callable(Progress): void $progress
     *
     * @return $this
     */
    public function progress(callable $progress): self
    {
        $httpClient = clone $this;

        $httpClient->options = $httpClient->options->onProgress($progress);

        return $httpClient;
    }

    /**
     * @param string|resource $filePath
     *
     * @return $this
     */
    public function saveToFile($filePath): self
    {
        $httpClient = clone $this;

        $httpClient->options = $httpClient->options->buffer($filePath);

        return $httpClient;
    }

    /**
     * @return $this
     */
    public function appendToFile(string $filePath): self
    {
        $httpClient = clone $this;

        $resource = fopen($filePath, 'a+b');

        if (false === $resource) {
            throw new InvalidArgumentException(sprintf('Could not open file "%s" for writing', $filePath));
        }

        $httpClient->options = $httpClient->options->buffer($resource);

        return $httpClient;
    }

    /**
     * @return $this
     */
    public function uploadFile(string $fieldName, string $filepath): self
    {
        $data = DataPart::fromPath($filepath);

        $httpClient = clone $this;
        $httpClient->options = $httpClient->options->addFile($fieldName, $data);

        return $httpClient;
    }

    /**
     * @return $this
     */
    public function httpVersion(string $httpVersion): self
    {
        $httpClient = clone $this;
        $httpClient->options->httpVersion = $httpVersion;

        return $httpClient;
    }

    /**
     * @return $this
     */
    public function http2(): self
    {
        $httpClient = clone $this;
        $httpClient->options->httpVersion = HttpClient::HTTP_VERSION_2;

        return $httpClient;
    }

    /**
     * @return $this
     */
    public function setBaseUri(string $url): self
    {
        $httpClient = clone $this;
        $httpClient->plugins[] = new BaseUriPlugin(
            Psr17FactoryDiscovery::findUriFactory()->createUri($url)
        );

        return $httpClient;
    }
}
