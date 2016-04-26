<?php
require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

$redis = new Predis\Client();

$app->get('/blog/{id}', function (Silex\Application $app, Request $request, $id) {
    // ...
});


$app->run();