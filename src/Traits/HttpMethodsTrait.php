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

use SolidWorx\ApiFy\HttpClient;

trait HttpMethodsTrait
{
    /** @var string */
    private $method = HttpClient::METHOD_GET;

    /**
     * @return $this
     */
    public function get(): self
    {
        return $this->method(HttpClient::METHOD_GET);
    }

    /**
     * @return $this
     */
    public function post(): self
    {
        return $this->method(HttpClient::METHOD_POST);
    }

    /**
     * @return $this
     */
    public function put(): self
    {
        return $this->method(HttpClient::METHOD_PUT);
    }

    /**
     * @return $this
     */
    public function patch(): self
    {
        return $this->method(HttpClient::METHOD_PATCH);
    }

    /**
     * @return $this
     */
    public function options(): self
    {
        return $this->method(HttpClient::METHOD_OPTIONS);
    }

    /**
     * @return $this
     */
    public function delete(): self
    {
        return $this->method(HttpClient::METHOD_DELETE);
    }

    /**
     * @return $this
     */
    public function method(string $method): self
    {
        $request = clone $this;
        $request->method = \strtoupper($method);

        return $request;
    }
}
