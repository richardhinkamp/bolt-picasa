#!/usr/bin/env php
<?php

require_once( __DIR__ . '/../../../../src/bootstrap.php' );

use Symfony\Component\Console\Application;

$application = new Application();

$application->setName("Bolt Picasa console tool");
$application->setVersion($app->getVersion());
$application->add(new BoltPicasa\Console\SyncPicasa($app));
$application->run();
