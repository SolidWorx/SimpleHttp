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

use Closure;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Traversable;

final class RequestOptions
{
    /** @var string|null */
    private $auth_basic = HttpClientInterface::OPTIONS_DEFAULTS['auth_basic'];

    /** @var string|null */
    private $auth_bearer = HttpClientInterface::OPTIONS_DEFAULTS['auth_bearer'];

    /** @var array */
    private $query = HttpClientInterface::OPTIONS_DEFAULTS['query'];

    /** @var array */
    private $headers = HttpClientInterface::OPTIONS_DEFAULTS['headers'];

    /** @var string|array|Closure|resource|Traversable */
    private $body = HttpClientInterface::OPTIONS_DEFAULTS['body'];

    /** @var mixed */
    private $json = HttpClientInterface::OPTIONS_DEFAULTS['json'];

    /** @var mixed */
    private $user_data = HttpClientInterface::OPTIONS_DEFAULTS['user_data'];

    /** @var int|null */
    private $max_redirects = HttpClientInterface::OPTIONS_DEFAULTS['max_redirects'];

    /** @var string|null */
    private $http_version = HttpClientInterface::OPTIONS_DEFAULTS['http_version'];

    /** @var string|null */
    private $base_uri = HttpClientInterface::OPTIONS_DEFAULTS['base_uri'];

    /** @var bool|resource|Closure */
    private $buffer = HttpClientInterface::OPTIONS_DEFAULTS['buffer'];

    /** @var null|callable(int, int, array) */
    private $on_progress = HttpClientInterface::OPTIONS_DEFAULTS['on_progress'];

    /** @var array */
    private $resolve = HttpClientInterface::OPTIONS_DEFAULTS['resolve'];

    /** @var string|null */
    private $proxy = HttpClientInterface::OPTIONS_DEFAULTS['proxy'];

    /** @var string|null */
    private $no_proxy = HttpClientInterface::OPTIONS_DEFAULTS['no_proxy'];

    /** @var string|null */
    private $timeout = HttpClientInterface::OPTIONS_DEFAULTS['timeout'];

    /** @var float */
    private $max_duration = HttpClientInterface::OPTIONS_DEFAULTS['max_duration'];

    /** @var string|null */
    private $bindto = HttpClientInterface::OPTIONS_DEFAULTS['bindto'];

    /** @var bool */
    private $verify_peer = HttpClientInterface::OPTIONS_DEFAULTS['verify_peer'];

    /** @var bool */
    private $verify_host = HttpClientInterface::OPTIONS_DEFAULTS['verify_host'];

    /** @var string|null */
    private $cafile = HttpClientInterface::OPTIONS_DEFAULTS['cafile'];

    /** @var string|null */
    private $capath = HttpClientInterface::OPTIONS_DEFAULTS['capath'];

    /** @var string|null */
    private $local_cert = HttpClientInterface::OPTIONS_DEFAULTS['local_cert'];

    /** @var string|null */
    private $local_pk = HttpClientInterface::OPTIONS_DEFAULTS['local_pk'];

    /** @var string|null */
    private $passphrase = HttpClientInterface::OPTIONS_DEFAULTS['passphrase'];

    /** @var string|null */
    private $ciphers = HttpClientInterface::OPTIONS_DEFAULTS['ciphers'];

    /** @var string|null */
    private $peer_fingerprint = HttpClientInterface::OPTIONS_DEFAULTS['peer_fingerprint'];

    /** @var bool */
    private $capture_peer_cert_chain = HttpClientInterface::OPTIONS_DEFAULTS['capture_peer_cert_chain'];

    /** @var array */
    private $extra = HttpClientInterface::OPTIONS_DEFAULTS['extra'];

    public function verifyHost(bool $verifyHost): self
    {
        $this->verify_host = $verifyHost;

        return $this;
    }

    public function verifyPeer(bool $verifyPeer): self
    {
        $this->verify_peer = $verifyPeer;

        return $this;
    }

    public function basicAuth(string $username, ?string $password = null): self
    {
        $this->auth_basic = \implode(':', [$username, $password]);

        return $this;
    }

    public function bearerAuth(string $token): self
    {
        $this->auth_bearer = $token;

        return $this;
    }

    public function build(): array
    {
        $options = [];

        foreach (get_object_vars($this) as $key => $value) {
            if ($value !== HttpClientInterface::OPTIONS_DEFAULTS[$key]) {
                $options[$key] = $value;
            }
        }

        return $options;
    }

    /**
     * @param string|array|Closure|resource|Traversable $body
     */
    public function body($body): self
    {
        $this->body = $body;

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

    public function addHeader(string $key, ?string $value = null): self
    {
        $this->headers[$key] = $value;

        return $this;
    }

    public function onProgress(callable $progress): self
    {
        $this->on_progress = $progress;

        return $this;
    }

    /**
     * @param bool|resource|Closure $resource
     *
     * @return $this
     */
    public function buffer($resource): self
    {
        $this->buffer = $resource;

        return $this;
    }
}
