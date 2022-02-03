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

final class Progress
{
    private int $downloaded;

    private int $totalSize;

    public function __construct(int $downloaded, int $totalSize)
    {
        $this->downloaded = $downloaded;
        $this->totalSize = $totalSize;
    }

    public function getDownloaded(): int
    {
        return $this->downloaded;
    }

    public function getTotalSize(): int
    {
        return $this->totalSize;
    }
}
