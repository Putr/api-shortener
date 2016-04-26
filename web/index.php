<?php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();

$redis = new Predis\Client();

require('engine.php');
require('api.php');

die("OK, terminating - remove for production!"); // @todo REMOVE DEV CODE

$app->run();