<?php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Yaml\Parser;

$app = new Silex\Application();

$app['domain'] = $_SERVER['HTTP_HOST'];

$app['redis'] = $app->share(function() {
	return new Predis\Client();
});

//
// Load configuration
// 
$config = $app['redis']->get('pirat_config');
$config = json_decode($config, true);
if ($config === null) {
	$defaultConfig = [
		'log_hits' => true,
		'debug'    => false
	];
	$yaml = new Parser();
	$config = $yaml->parse(file_get_contents(__DIR__ . '/../config.yml'));
	$config = array_merge($defaultConfig, $config);
	$app['redis']->set('pirat_config', json_encode($config));
}
$app['config'] = $config;
$app['debug'] = $config['debug'];

//
// Verify domain
//
if (!array_key_exists($app['domain'], $app['config']['domains'])) {
	$app->redirect('/error/invalid-domain');
	die('Invalid domain');
}

// 
// Include res of the code
// 
require('engine.php');
require('api.php');
require('errors.php');


$app->run();