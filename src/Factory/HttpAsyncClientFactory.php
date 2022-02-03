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

namespace SolidWorx\ApiFy\Factory;

use Http\Client\HttpAsyncClient;
use Psr\Http\Client\ClientInterface;
use SolidWorx\ApiFy\RequestOptions;

interface HttpAsyncClientFactory
{
    public static function createInstance(RequestOptions $requestOptions, ?ClientInterface $client = null): HttpAsyncClient;
}
