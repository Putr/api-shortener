<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// @todo THIS IS UNTESTED
$app->post('/api/v1/url', function (Silex\Application $app, Request $request) use ($redis, $config) {
	$ac = $request->get('access_code');
	$slug = $request->get('slug');
	$url = $request->get('target_url');
	
	if (empty($ac)) {
		return new Response('Missing access code', 400);
	}

	if (empty($slug)) {
		return new Response('Missing short URL (slug)', 400);
	}

	if (empty($url)) {
		return new Response('Missing target url', 400);
	}

	$acHash = sha1($ac);
	foreach ($config['access_codes'] as $label => $hash) {
		if ($acHash === $hash) {
			$redis->set('url_'.$slug, $url);
			$redis->set('meta_'.$slug, json_encode(
					['creator'   => $label,
					 'timestamp' => time()
					])
			);
			return new Response('', 200);
		}
	}

	return new Response('Access code is invalid.', 403);

});

$app->get('/api/v1/url/{slug}', function (Silex\Application $app, Request $request, $slug) use ($redis) {
	
});

$app->delete('/api/v1/url/{slug}', function (Silex\Application $app, Request $request, $slug) use ($redis) {
    $ac = $request->get('access_code');

});