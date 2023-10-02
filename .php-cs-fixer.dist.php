<?php
/*
 * This document has been generated with
 * https://mlocati.github.io/php-cs-fixer-configurator/#version:3.1.0|configurator
 * you can change this configuration by importing this file.
 */
$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@DoctrineAnnotation' => false,
        '@PSR1' => true,
        '@PSR12' => true,
        '@PSR2' => true,
        '@Symfony' => true,
    ])
    ->setFinder(PhpCsFixer\Finder::create()
        ->exclude(['vendor', 'cache', 'node_modules'])
        ->in(__DIR__)
    )
;
