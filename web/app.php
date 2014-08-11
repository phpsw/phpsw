<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../app/app.php';

$app['cli'] = false;

$app->register(new Silex\Provider\TwigServiceProvider, [
    'twig.path' => __DIR__ . '/../views'
]);

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addExtension(new PHPSW\Twig\ThumborExtension($app['thumbor.builder']));
    $twig->addExtension(new Twig_Extensions_Extension_Text($app));

    return $twig;
}));

$app->run();
