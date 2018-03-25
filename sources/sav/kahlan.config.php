<?php
use Kahlan\Filter\Filters;
use Kahlan\Reporter\Coverage;
use Kahlan\Reporter\Coverage\Driver\Xdebug;
use Kahlan\Reporter\Coverage\Exporter\Coveralls;
use Kahlan\Reporter\Coverage\Exporter\CodeClimate;

$commandLine = $this->commandLine();
$commandLine->option('coverage', 'default', 4);

Filters::apply($this, 'coverage', function($next) {
    if (!extension_loaded('xdebug')) {
        return;
    }
    $reporters = $this->reporters();
    $coverage = new Coverage([
        'verbosity' => $this->commandLine()->get('coverage'),
        'driver'    => new Xdebug(),
        'path'      => $this->commandLine()->get('src'),
        'exclude'   => [
        ],
        'colors'    => !$this->commandLine()->get('no-colors')
    ]);
    $reporters->add('coverage', $coverage);
});

Filters::apply($this, 'reporting', function($next) {
    $reporter = $this->reporters()->get('coverage');
    if (!$reporter) {
        return;
    }
    Coveralls::write([
        'collector'      => $reporter,
        'file'           => 'coveralls.json',
        'service_name'   => 'travis-ci',
        'service_job_id' => getenv('TRAVIS_JOB_ID') ?: null
    ]);
    CodeClimate::write([
        'collector'  => $reporter,
        'file'       => 'codeclimate.json',
        'branch'     => getenv('TRAVIS_BRANCH') ?: null,
        'repo_token' => '0c1bc762946317b2af76b73923592cf6b57adab4c21cd1ed34d988265689b3fb'
    ]);
    return $next();
});
