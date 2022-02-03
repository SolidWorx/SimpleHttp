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

use Exception;
use Http\Promise\Promise;
use function is_array;
use function json_decode;
use const JSON_THROW_ON_ERROR;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use SolidWorx\SimpleHttp\Exception\NotImplementedException;

final class Response implements ResponseInterface
{
    private Promise $response;

    public function __construct(Promise $response)
    {
        $this->response = $response;
    }

    /**
     * @throws Exception
     */
    public function getStatusCode(): int
    {
        /** @var ResponseInterface $response */
        $response = $this->response->wait();

        return $response->getStatusCode();
    }

    /**
     * @throws Exception
     */
    public function getHeaders(): array
    {
        /** @var ResponseInterface $response */
        $response = $this->response->wait();

        return $response->getHeaders();
    }

    /**
     * @throws Exception
     */
    public function getContent(): string
    {
        return $this->getBody()->getContents();
    }

    /**
     * @throws JsonException|Exception
     */
    public function toArray(): array
    {
        /** @var ResponseInterface $response */
        $response = $this->response->wait();

        $body = $response->getBody()->getContents();
        $result = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($result)) {
            throw new JsonException('Unable to decode JSON response');
        }

        return $result;
    }

    /**
     * @throws Exception
     */
    public function getProtocolVersion(): string
    {
        /** @var ResponseInterface $response */
        $response = $this->response->wait();

        return $response->getProtocolVersion();
    }

    public function withProtocolVersion($version): Response
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * @throws Exception
     */
    public function hasHeader($name): bool
    {
        /** @var ResponseInterface $response */
        $response = $this->response->wait();

        return $response->hasHeader($name);
    }

    /**
     * @throws Exception
     */
    public function getHeader($name): array
    {
        /** @var ResponseInterface $response */
        $response = $this->response->wait();

        return $response->getHeader($name);
    }

    /**
     * @throws Exception
     */
    public function getHeaderLine($name): string
    {
        /** @var ResponseInterface $response */
        $response = $this->response->wait();

        return $response->getHeaderLine($name);
    }

    public function withHeader($name, $value): Response
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function withAddedHeader($name, $value): Response
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function withoutHeader($name): Response
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * @throws Exception
     */
    public function getBody(): StreamInterface
    {
        /** @var ResponseInterface $response */
        $response = $this->response->wait();

        return $response->getBody();
    }

    public function withBody(StreamInterface $body): Response
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function withStatus($code, $reasonPhrase = ''): Response
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * @throws Exception
     */
    public function getReasonPhrase(): string
    {
        /** @var ResponseInterface $response */
        $response = $this->response->wait();

        return $response->getReasonPhrase();
    }

    /**
     * @throws Exception
     */
    public function __destruct()
    {
        if (Promise::FULFILLED !== $this->response->getState()) {
            /** @var ResponseInterface $response */
            $response = $this->response->wait();
            $response->getBody()->close();
        }
    }
}
