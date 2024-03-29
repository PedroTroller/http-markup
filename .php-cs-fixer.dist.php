<?php

declare(strict_types=1);

use PedroTroller\CS\Fixer\Fixers;
use PedroTroller\CS\Fixer\RuleSetFactory;

$config = new PhpCsFixer\Config();
$config
    ->setRiskyAllowed(true)
    ->setRules(RuleSetFactory::create()
        ->symfony(true)
        ->php(7.2, true)
        ->pedrotroller(true)
        ->enable('ordered_imports')
        ->enable('align_multiline_comment')
        ->enable('array_indentation')
        ->enable('no_superfluous_phpdoc_tags')
        ->enable('binary_operator_spaces', [
            'operators' => [
                '='  => 'align_single_space_minimal',
                '=>' => 'align_single_space_minimal',
            ],
        ])
        ->getRules()
    )
    ->setUsingCache(false)
    ->registerCustomFixers(new Fixers())
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__.'/html')
            ->append([__FILE__])
    )
;

return $config;
