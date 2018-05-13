<?php

use Sami\Parser\Filter\TrueFilter;
use Sami\RemoteRepository\GitHubRemoteRepository;
use Sami\Sami;
use Sami\Version\GitVersionCollection;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->exclude('Assets')
    ->exclude('Data')
    ->exclude('Table')
    ->exclude('Translation')
    ->exclude('View')
    ->exclude('XMLView')
    ->in($dir = 'Core')
;

$versions = GitVersionCollection::create($dir)
    //->addFromTags('v2.0.*')
    //->add('2.0', '2.0 branch')
    //->add('master', 'master branch')
    ->add('a-lot-of-changes', 'master branch')
;

$sam = new Sami(
    $iterator,
    [
        'theme'                => 'default',
        'versions'             => $versions,
        'title'                => 'FacturaScripts',
        'build_dir'            => __DIR__.'/doc/%version%',
        'cache_dir'            => __DIR__.'/cache/doc/%version%',
        'remote_repository'    => new GitHubRemoteRepository('NeoRazorX/facturascripts', 'Core'),
        'default_opened_level' => 2,
    ]
);
// document all methods and properties
$sam['filter'] = function () {
    return new TrueFilter();
};

return $sam;

/**
 * How to use it:
 * curl -O http://get.sensiolabs.org/sami.phar
 * php sami.phar generateDoc.php
 */