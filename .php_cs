<?php

declare(strict_types=1);

/**
 * This file is part of SolidWorx/Apify project.
 * Copyright (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 */

$header = <<<'EOF'
This file is part of SolidWorx/Apify project.

Copyright (c) Pierre du Plessis <open-source@solidworx.co>

This source file is subject to the MIT license that is bundled
with this source code in the file LICENSE.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->in(['src', 'tests']);

return PhpCsFixer\Config::create()
    ->setRules(
        [
            '@PSR1' => true,
            '@PSR2' => true,
            '@Symfony' => true,
            'array_syntax' => array('syntax' => 'short'),
            'phpdoc_no_package' => true,
            'phpdoc_summary' => false,
            'declare_strict_types' => true,
            'strict_param' => true,
            'header_comment' => [
                'comment_type' => 'comment',
                'header' => \trim($header),
                'location' => 'after_declare_strict',
                'separate' => 'both',
            ],
        ]
    )
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;
