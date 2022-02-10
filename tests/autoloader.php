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

require_once __DIR__.'/../vendor/autoload.php';

if (file_exists(__DIR__.'/tools/flysystemv1/vendor/autoload.php')) {
    require_once __DIR__.'/tools/flysystemv1/vendor/autoload.php';
}

if (file_exists(__DIR__.'/tools/flysystemv2/vendor/autoload.php')) {
    require_once __DIR__.'/tools/flysystemv2/vendor/autoload.php';
}
