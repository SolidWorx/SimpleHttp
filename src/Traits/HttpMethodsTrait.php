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

use SolidWorx\SimpleHttp\Enum\HttpMethod;
use SolidWorx\SimpleHttp\HttpClient;

trait HttpMethodsTrait
{
    private string $method = 'GET';

    /**
     * @return $this
     */
    public function get(): self
    {
        return $this->method(HttpMethod::GET->value);
    }

    /**
     * @return $this
     */
    public function post(): self
    {
        return $this->method(HttpMethod::POST->value);
    }

    /**
     * @return $this
     */
    public function put(): self
    {
        return $this->method(HttpMethod::PUT->value);
    }

    /**
     * @return $this
     */
    public function patch(): self
    {
        return $this->method(HttpMethod::PATCH->value);
    }

    /**
     * @return $this
     */
    public function options(): self
    {
        return $this->method(HttpMethod::OPTIONS->value);
    }

    /**
     * @return $this
     */
    public function delete(): self
    {
        return $this->method(HttpMethod::DELETE->value);
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
