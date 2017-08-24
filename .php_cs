<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in('scripts')
    ->in('src')
    ->in('tests');

return Config::create()
    ->setFinder($finder)
    ->setRules([
      '@Symfony' => true,
    ])
    ->setUsingCache(true);
