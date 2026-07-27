<?php

namespace Zerp\Jitsi\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Zerp\Jitsi\Providers\JitsiServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [JitsiServiceProvider::class];
    }
}
