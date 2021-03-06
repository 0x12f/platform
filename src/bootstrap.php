<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/vars.php';

/**
 * @var \Psr\Container\ContainerInterface $c
 * @var \Psr\Container\ContainerInterface $container
 */

// app container
$c = $container = new \Slim\Container((array) require_once SRC_DIR . '/settings.php');

RunTracy\Helpers\Profiler\Profiler::start('init dependencies');

// set up dependencies
require SRC_DIR . '/dependencies.php';

RunTracy\Helpers\Profiler\Profiler::finish('init dependencies');
RunTracy\Helpers\Profiler\Profiler::start('init plugins');

// include plugins
require PLUGIN_DIR . '/installed.php';

RunTracy\Helpers\Profiler\Profiler::finish('init plugins');

$app = new \Slim\App($container);
