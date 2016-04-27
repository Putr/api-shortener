<?php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Yaml\Parser;

$app = new Silex\Application();
$app['debug'] = true;

$redis = new Predis\Client();

$config = $redis->get('pirat_config');
$config = json_decode($config, true);

if ($config === null) {
	$yaml = new Parser();
	$config = $yaml->parse(file_get_contents(__DIR__ . '/../access.yml'));
	$redis->set('pirat_config', json_encode($config));
}

require('engine.php');
require('api.php');

// die("OK, terminating - remove for production!"); // @todo REMOVE DEV CODE

$app->run();