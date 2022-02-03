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

namespace SolidWorx\SimpleHttp\Header;

final class RequestHeader
{
    // Standard request headers
    public const A_IM = 'A-IM';
    public const Accept = 'Accept';
    public const Accept_Charset = 'Accept-Charset';
    public const Accept_Datetime = 'Accept-Datetime';
    public const Accept_Encoding = 'Accept-Encoding';
    public const Accept_Language = 'Accept-Language';
    public const Access_Control_Request_Method = 'Access-Control-Request-Method';
    public const Access_Control_Request_Headers = 'Access-Control-Request-Headers';
    public const Authorization = 'Authorization';
    public const Cache_Control = 'Cache-Control';
    public const Connection = 'Connection';
    public const Content_Encoding = 'Content-Encoding';
    public const Content_Length = 'Content-Length';
    public const Content_MD5 = 'Content-MD5';
    public const Content_Type = 'Content-Type';
    public const Cookie = 'Cookie';
    public const Date = 'Date';
    public const Expect = 'Expect';
    public const Forwarded = 'Forwarded';
    public const From = 'From';
    public const Host = 'Host';
    public const HTTP2_Settings = 'HTTP2-Settings';
    public const If_Match = 'If-Match';
    public const If_Modified_Since = 'If-Modified-Since';
    public const If_None_Match = 'If-None-Match';
    public const If_Range = 'If-Range';
    public const If_Unmodified_Since = 'If-Unmodified-Since';
    public const Max_Forwards = 'Max-Forwards';
    public const Origin = 'Origin';
    public const Pragma = 'Pragma';
    public const Prefer = 'Prefer';
    public const Proxy_Authorization = 'Proxy-Authorization';
    public const Range = 'Range';
    public const Referer = 'Referer';
    public const TE = 'TE';
    public const Trailer = 'Trailer';
    public const Transfer_Encoding = 'Transfer-Encoding';
    public const User_Agent = 'User-Agent';
    public const Upgrade = 'Upgrade';
    public const Via = 'Via';
    public const Warning = 'Warning';

    // Common non-standard request headers
    public const Upgrade_Insecure_Requests = 'Upgrade-Insecure-Requests';
    public const DNT = 'DNT';
    public const X_Forwarded_For = 'X-Forwarded-For';
    public const X_Forwarded_Host = 'X-Forwarded-Host';
    public const X_Forwarded_Proto = 'X-Forwarded-Proto';
    public const Front_End_Https = 'Front-End-Https';
    public const X_Http_Method_Override = 'X-Http-Method-Override';
    public const X_ATT_DeviceId = 'X-ATT-DeviceId';
    public const X_Wap_Profile = 'X-Wap-Profile';
    public const Proxy_Connection = 'Proxy-Connection';
    public const X_UIDH = 'X-UIDH';
    public const X_Csrf_Token = 'X-Csrf-Token';
    public const X_Request_ID = 'X-Request-ID';
    public const X_Correlation_ID = 'X-Correlation-ID';
    public const Save_Data = 'Save-Data';
    public const X_REQUESTED_WITH = 'X-REQUESTED-WITH';
}
