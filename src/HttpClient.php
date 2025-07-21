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
use SolidWorx\SimpleHttp\Enum\HttpMethod;
use SolidWorx\SimpleHttp\Enum\HttpVersion;

final class HttpClient
{
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
