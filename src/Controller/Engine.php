<?php

namespace Controller;

use Symfony\Component\HttpFoundation\Request;

class Engine {
	public function useShortUrl(\Silex\Application $app, Request $request, $shortUrl) {
	    $url = $app['redis']->get(sprintf('%s_url_%s', $app['domain'], $shortUrl));

	    if (is_null($url)) {
	    	return $app->redirect('/error/' . $shortUrl);
	    }

	    // Logs number of clicks
	    $app['redis']->incr(sprintf('%s_num_%s_%s', $app['domain'], $shortUrl, date('Ymd')));
	    $app['redis']->incr(sprintf('%s_num_%s_all', $app['domain'], $shortUrl));

	    if ($app['config']['log_hits']) {
	        $logLine = sprintf("%s::%s::%s::%s::%s" . PHP_EOL, date('c'), $app['domain'], $shortUrl, $url, $_SERVER['REMOTE_ADDR']);
	        file_put_contents(__DIR__ . '/../../log/hits.log', $logLine, FILE_APPEND);
	    }

	    return $app->redirect($url);

	}
}