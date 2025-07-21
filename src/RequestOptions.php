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

use SolidWorx\SimpleHttp\Enum\HttpVersion;
use SolidWorx\SimpleHttp\Enum\RequestHeader;
use Symfony\Component\Mime\Header\HeaderInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

final class RequestOptions
{
    public array $files = [];

    public ?string $authBasic = null;

    public ?string $authBearer = null;

    public array $query = [];

    /**
     * @var array<string, string>
     */
    public array $headers = [];

    public string $body = '';

    public $json;

    public $userData;

    public ?int $maxRedirects = null;

    public HttpVersion $httpVersion = HttpVersion::HTTP_1_1;

    public ?string $baseUri = null;

    /** @var string|resource|null */
    public $buffer;

    /** @var callable(Progress): void|null */
    public $onProgress;

    public ?array $resolve = null;

    public ?string $proxy = null;

    public ?string $noProxy = null;

    public ?string $timeout = null;

    public ?float $maxDuration = null;

    public ?string $bindTo = null;

    public ?bool $verifyPeer = null;

    public ?bool $verifyHost = null;

    public ?string $cafile = null;

    public ?string $capath = null;

    public ?string $localCert = null;

    public ?string $localPk = null;

    public ?string $passphrase = null;

    public ?string $ciphers = null;

    public ?string $peerFingerprint = null;

    public ?bool $capturePeerCertChain = null;

    public ?array $extra = null;

    public function verifyHost(bool $verifyHost): self
    {
        $this->verifyHost = $verifyHost;

        return $this;
    }

    public function verifyPeer(bool $verifyPeer): self
    {
        $this->verifyPeer = $verifyPeer;

        return $this;
    }

    public function basicAuth(string $username, ?string $password = null): self
    {
        $this->authBasic = \implode(':', [$username, $password]);

        return $this;
    }

    public function bearerAuth(string $token): self
    {
        $this->authBearer = $token;

        return $this;
    }

    public function getBody(): string
    {
        if ([] !== $this->files) {
            $body = $this->body;
            $this->body = '';

            $formData = new FormDataPart(\array_merge((array) $body, $this->files));

            $headers = $formData->getPreparedHeaders();

            foreach ($headers->getNames() as $name) {
                /** @var HeaderInterface $header */
                $header = $headers->get($name);
                $this->addHeader($name, $header->getBodyAsString());
            }

            return $formData->bodyToString();
        }

        return $this->body;
    }

    public function body(array|string $body): self
    {
        if (\is_array($body)) {
            $this->body = \http_build_query($body, '', '&');
            $this->addHeader(RequestHeader::CONTENT_TYPE, 'application/x-www-form-urlencoded');
        } elseif (\is_string($body)) {
            $this->body = $body;
        }

        return $this;
    }

    public function json(array $json): self
    {
        $this->json = $json;

        return $this;
    }

    public function query(array $queryParams): self
    {
        $this->query = $queryParams;

        return $this;
    }

    public function addHeader(string|RequestHeader $key, string $value): self
    {
        $this->headers[\is_string($key) ? $key : $key->value] = $value;

        return $this;
    }

    /**
     * @param callable(Progress): void $progress
     */
    public function onProgress(callable $progress): self
    {
        $this->onProgress = $progress;

        return $this;
    }

    /**
     * @param string|resource $resource
     */
    public function buffer($resource): self
    {
        $this->buffer = $resource;

        return $this;
    }

    public function addFile(string $fieldName, DataPart $file): self
    {
        $this->files[$fieldName] = $file;

        return $this;
    }

    public function httpVersion(string|HttpVersion $httpVersion): self
    {
        $this->httpVersion = \is_string($httpVersion) ? HttpVersion::from($httpVersion) : $httpVersion;

        return $this;
    }
}
