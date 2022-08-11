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

$header = <<<'EOF'
This file is part of SolidWorx/SimpleHttp project.

Copyright (c) Pierre du Plessis <open-source@solidworx.co>

This source file is subject to the MIT license that is bundled
with this source code in the file LICENSE.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->in(['src', 'tests']);

return (new PhpCsFixer\Config())
    ->setRules(
        [
            '@PSR1' => true,
            '@PSR2' => true,
            '@Symfony' => true,
            'array_syntax' => ['syntax' => 'short'],
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
            'ordered_imports' => [
                'imports_order' => ['const', 'class', 'function'],
            ],
        ]
    )
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;
