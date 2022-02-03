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

use Psr\Http\Client\ClientInterface;

final class HttpClient
{
    public const HTTP_VERSION_1 = '1.1';
    public const HTTP_VERSION_2 = '2.0';

    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_PATCH = 'PATCH';
    public const METHOD_OPTIONS = 'OPTIONS';
    public const METHOD_DELETE = 'DELETE';
    public const METHOD_HEAD = 'HEAD';
    public const METHOD_TRACE = 'TRACE';
    public const METHOD_CONNECT = 'CONNECT';

    private ?ClientInterface $client = null;

    public function __construct(?ClientInterface $client = null)
    {
        $this->client = $client;
    }

    public static function create(?ClientInterface $client = null): RequestBuilder
    {
        $self = new self($client);

        return new RequestBuilder($self->client);
    }

    public static function createForBaseUrl(string $url, ?ClientInterface $client = null): RequestBuilder
    {
        $self = new self($client);

        return (new RequestBuilder($self->client))
            ->url($url);
    }
}
