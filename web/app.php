<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application;

if (strpos(__DIR__, 'phpsw.org.uk') !== false) {
    $env = 'prod';
} else {
    $env = 'dev';
}

$app->get('/', 'PHPSW\Controller\AppController::indexAction');

foreach (['app', $env, 'secrets'] as $config) {
    $app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__ . '/../config/' . $config . '.yml'));
}

$app->register(new Silex\Provider\TwigServiceProvider, [
    'twig.path' => __DIR__ . '/../views'
]);

$app->run();
