<?php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();

$redis = new Predis\Client();

$app->get('/error/{slug}', function (Silex\Application $app, Request $request, $slug) {
    return "This short URL does not exsist";

});

$app->get('/{slug}', function (Silex\Application $app, Request $request, $slug) use ($redis) {
    $url = $redis->get('url_' . $slug);

    if (is_null($url)) {
    	return $app->redirect('/error/' . $slug);
    }

    // Logs number of clicks
    $redis->incr('num_'.$slug);

    $logLine = sprintf("%s::%s::%s::%s" . PHP_EOL, date('c'), $slug, $url, $_SERVER['REMOTE_ADDR']);
    file_put_contents(__DIR__ . '/../log/hits.log', $logLine, FILE_APPEND);

    return $app->redirect($url);

});


$app->run();