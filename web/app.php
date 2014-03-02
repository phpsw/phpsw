<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application(['debug' => true]);

$app->get('/', 'PHPSW\Controller\AppController::indexAction');

$app->register(new Silex\Provider\TwigServiceProvider, [
    'twig.path' => __DIR__ . '/../views'
]);

$app->run();
