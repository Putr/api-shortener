<?php
require_once __DIR__.'/../vendor/autoload.php';

$redis = new Predis\Client();

$redis->del('pirat_config');