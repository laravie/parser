<?php

$finder = PhpCsFixer\Finder::create()
            ->in(__DIR__.'/src')
            ->in(__DIR__.'/tests');

return PhpCsFixer\Config::create()
            ->setRiskyAllowed(false)
            ->setRules([
                '@Symfony' => true,
                'no_empty_comment' => false,
                'no_extra_consecutive_blank_lines' => false,
                'not_operator_with_successor_space' => true,
                'ordered_imports' => ['sortAlgorithm' => 'length'],
                'phpdoc_align' => false,
                'phpdoc_no_empty_return' => false,
                'yoda_style' => false,
            ])
            ->setFinder($finder);
