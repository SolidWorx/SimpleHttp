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

use Symfony\Contracts\HttpClient\ResponseInterface;

final class Response implements ResponseInterface
{
    /** @var ResponseInterface */
    private $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function getHeaders(bool $throw = true): array
    {
        return $this->response->getHeaders($throw);
    }

    public function getContent(bool $throw = true): string
    {
        return $this->response->getContent($throw);
    }

    public function toArray(bool $throw = true): array
    {
        return $this->response->toArray($throw);
    }

    public function cancel(): void
    {
        $this->response->cancel();
    }

    public function getInfo(string $type = null): array
    {
        return $this->response->getInfo($type);
    }

    public function isCanceled(): bool
    {
        return $this->response->getInfo('canceled');
    }

    public function getError(): ?string
    {
        return $this->response->getInfo('error');
    }

    public function getHttpCode(): int
    {
        return $this->response->getInfo('http_code');
    }

    public function getHttpMethod(): string
    {
        return $this->response->getInfo('http_method');
    }

    public function getRedirectCount(): int
    {
        return $this->response->getInfo('redirect_count');
    }

    public function getRedirectUrl(): ?string
    {
        return $this->response->getInfo('redirect_url');
    }

    public function getResponseHeaders(): array
    {
        return $this->response->getInfo('response_headers');
    }

    public function getStartTime(): float
    {
        return $this->response->getInfo('start_time');
    }

    public function getUrl(): string
    {
        return $this->response->getInfo('url');
    }

    /**
     * @return mixed
     */
    public function getUserData()
    {
        return $this->response->getInfo('user_data');
    }
}
