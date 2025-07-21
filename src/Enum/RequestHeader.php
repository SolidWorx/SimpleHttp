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

namespace SolidWorx\SimpleHttp\Enum;

/**
 * HTTP Request Header enum
 */
enum RequestHeader: string
{
    // Standard request headers
    case A_IM = 'A-IM';
    case ACCEPT = 'Accept';
    case ACCEPT_CHARSET = 'Accept-Charset';
    case ACCEPT_DATETIME = 'Accept-Datetime';
    case ACCEPT_ENCODING = 'Accept-Encoding';
    case ACCEPT_LANGUAGE = 'Accept-Language';
    case ACCESS_CONTROL_REQUEST_METHOD = 'Access-Control-Request-Method';
    case ACCESS_CONTROL_REQUEST_HEADERS = 'Access-Control-Request-Headers';
    case AUTHORIZATION = 'Authorization';
    case CACHE_CONTROL = 'Cache-Control';
    case CONNECTION = 'Connection';
    case CONTENT_ENCODING = 'Content-Encoding';
    case CONTENT_LENGTH = 'Content-Length';
    case CONTENT_MD5 = 'Content-MD5';
    case CONTENT_TYPE = 'Content-Type';
    case COOKIE = 'Cookie';
    case DATE = 'Date';
    case EXPECT = 'Expect';
    case FORWARDED = 'Forwarded';
    case FROM = 'From';
    case HOST = 'Host';
    case HTTP2_SETTINGS = 'HTTP2-Settings';
    case IF_MATCH = 'If-Match';
    case IF_MODIFIED_SINCE = 'If-Modified-Since';
    case IF_NONE_MATCH = 'If-None-Match';
    case IF_RANGE = 'If-Range';
    case IF_UNMODIFIED_SINCE = 'If-Unmodified-Since';
    case MAX_FORWARDS = 'Max-Forwards';
    case ORIGIN = 'Origin';
    case PRAGMA = 'Pragma';
    case PREFER = 'Prefer';
    case PROXY_AUTHORIZATION = 'Proxy-Authorization';
    case RANGE = 'Range';
    case REFERER = 'Referer';
    case TE = 'TE';
    case TRAILER = 'Trailer';
    case TRANSFER_ENCODING = 'Transfer-Encoding';
    case USER_AGENT = 'User-Agent';
    case UPGRADE = 'Upgrade';
    case VIA = 'Via';
    case WARNING = 'Warning';

    // Common non-standard request headers
    case UPGRADE_INSECURE_REQUESTS = 'Upgrade-Insecure-Requests';
    case DNT = 'DNT';
    case X_FORWARDED_FOR = 'X-Forwarded-For';
    case X_FORWARDED_HOST = 'X-Forwarded-Host';
    case X_FORWARDED_PROTO = 'X-Forwarded-Proto';
    case FRONT_END_HTTPS = 'Front-End-Https';
    case X_HTTP_METHOD_OVERRIDE = 'X-Http-Method-Override';
    case X_ATT_DEVICEID = 'X-ATT-DeviceId';
    case X_WAP_PROFILE = 'X-Wap-Profile';
    case PROXY_CONNECTION = 'Proxy-Connection';
    case X_UIDH = 'X-UIDH';
    case X_CSRF_TOKEN = 'X-Csrf-Token';
    case X_REQUEST_ID = 'X-Request-ID';
    case X_CORRELATION_ID = 'X-Correlation-ID';
    case SAVE_DATA = 'Save-Data';
    case X_REQUESTED_WITH = 'X-REQUESTED-WITH';
}
