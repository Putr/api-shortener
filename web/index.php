<?php
require_once __DIR__.'/../vendor/autoload.php';

// I HATE CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
	header('Access-Control-Allow-Methods: POST, PUT, DELETE, GET, HEAD');
	header('Access-Control-Allow-Headers: X-Requested-With');
  	exit;
}

use Symfony\Component\Yaml\Parser;

$app = new \Silex\Application();

$app['domain'] = $_SERVER['HTTP_HOST'];

$app['redis'] = $app->share(function() {
	return new Predis\Client();
});

$app['model'] = $app->share(function($app) {
	return new Model\Redis($app['redis']);
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
$app->get('/{shortUrl}', 'Controller\\Engine::useShortUrl');

$app->post('/api/v1/url/{domain}', 'Controller\\Api::createNewShortUrl');
$app->get('/api/v1/url/{domain}', 'Controller\\Api::getDomainUrls');
$app->get('/api/v1/url/{domain}/{shortUrl}', 'Controller\\Api::showShortUrl');
$app->delete('/api/v1/url/{domain}/{shortUrl}', 'Controller\\Api::deleteShortUrl');
$app->get('/api/v1/domain', 'Controller\\Api::getAllEnabledDomains');

$app->get('/error/{shortUrl}', 'Controller\\Error::missingShortUrl');
$app->get('/error/invalid-domain', 'Controller\\Error::domainNotEnabled');

$app->run();