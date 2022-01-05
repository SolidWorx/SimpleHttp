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

namespace SolidWorx\ApiFy\Traits;

use Closure;
use SolidWorx\ApiFy\HttpClient;
use SolidWorx\ApiFy\Progress;
use Symfony\Component\Mime\Part\DataPart;
use Traversable;

trait HttpOptionsTrait
{
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
        $httpClient->options = $httpClient->options->basicAuth($username, $password);

        return $httpClient;
    }

    /**
     * @return $this
     */
    public function bearerToken(string $token): self
    {
        $httpClient = clone $this;
        $httpClient->options = $httpClient->options->bearerAuth($token);

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
        $httpClient->options = $httpClient->options->query($queryParams);

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

        $httpClient->options = $httpClient->options->json($json);

        return $httpClient;
    }

    /**
     * @param callable(Progress): void $progress
     *
     * @return $this
     */
    public function progress(callable $progress): self
    {
        $httpClient = clone $this;

        $onProgress = static function (int $dlNow, int $dlSize, array $info) use ($progress): void {
            $progress(new Progress($dlNow, $dlSize, $info));
        };

        $httpClient->options = $httpClient->options->onProgress($onProgress);

        return $httpClient;
    }

    /**
     * @return $this
     */
    public function streamToFile(string $filePath): self
    {
        $httpClient = clone $this;

        $buffer = static function (array $headers) use ($filePath) {
            return fopen($filePath, 'c+b');
        };

        $httpClient->options = $httpClient->options->buffer($buffer);

        return $httpClient;
    }

    /**
     * @return $this
     */
    public function appendToFile(string $filePath): self
    {
        $httpClient = clone $this;

        $buffer = static function (array $headers) use ($filePath) {
            return fopen($filePath, 'a+b');
        };

        $httpClient->options = $httpClient->options->buffer($buffer);

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
        $httpClient->options = $httpClient->options->httpVersion($httpVersion);

        return $httpClient;
    }

    /**
     * @return $this
     */
    public function http2(): self
    {
        $httpClient = clone $this;
        $httpClient->options = $httpClient->options->httpVersion(HttpClient::HTTP_VERSION_2);

        return $httpClient;
    }
}
