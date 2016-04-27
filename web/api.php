<?php
use Symfony\Component\HttpFoundation\Request;

$app->post('/api/v1/url', function (Silex\Application $app, Request $request) use ($redis, $config) {
	$ac = $request->get('access_code');
	$shortUrl = $request->get('shortUrl');
	$url = $request->get('target_url');

	if (empty($ac)) {
		return $app->json(['error' => 'Missing access code'], 400);
	}

	if (empty($shortUrl)) {
		return $app->json(['error' => 'Missing short URL (shortUrl)'], 400);
	}

	if (empty($url)) {
		return $app->json(['error' => 'Missing target url'], 400);
	}

	if ($redis->get('url_'.$shortUrl) !== NULL) {
		return $app->json(['error' => 'Short Url already exsists.'], 400);
	}

	$acHash = sha1($ac);
	foreach ($config['access_codes'] as $label => $hash) {
		if ($acHash === $hash) {
			$redis->set('url_'.$shortUrl, $url);
			$redis->set('meta_'.$shortUrl, json_encode(
					['creator'   => $label,
					 'timestamp' => time()
					])
			);
			return $app->json(['success' => true], 200);
		}
	}

	return $app->json(['error' => 'Access code is invalid.'], 403);

});

$app->get('/api/v1/url/{shortUrl}', function (Silex\Application $app, Request $request, $shortUrl) use ($redis) {
	$meta = $redis->get('meta_' . $shortUrl);
	if ($meta === NULL) {
		return $app->json(['error' => 'Short url not found.'], 404);
	}
	$meta = json_decode($meta, true);

	$url = $redis->get('url_' . $shortUrl);
	$numAll = (integer) $redis->get(sprintf('num_%s_all', $shortUrl));
	$numToday = (integer) $redis->get(sprintf('num_%s_%s', $shortUrl, date('Ymd')));

	$today = (integer) date('Ymd');
	$numWeek = $numMonth = $numToday;

	for ($i=1; $i < 31; $i++) { 
		$thisDay = $redis->get(sprintf('num_%s_%s', $shortUrl, $today - $i));

		if ($thisDay === NULL) {
			$thisDay = 0;
		}
		if ($i < 8) {
			$numWeek += $thisDay;
		}
		$numMonth += $thisDay;

		if ($numMonth === $numAll) {
			break;
		}
	}

	$payload = [
		'creator' => $meta['creator'],
		'timestamp' => $meta['timestamp'],
		'target_url' => $url,
		'hits_today' => $numToday,
	    'hits_7days' => $numWeek,
	    'hits_30days' => $numMonth,
	    'hits_all' => $numAll
	];

	return $app->json($payload, 200);



});

$app->delete('/api/v1/url/{shortUrl}', function (Silex\Application $app, Request $request, $shortUrl) use ($redis, $config) {
    $ac = $request->get('access_code');

    if (empty($ac)) {
		return $app->json(['error' => 'Missing access code'], 400);
	}

	$acHash = sha1($ac);
	foreach ($config['access_codes'] as $label => $hash) {
		if ($acHash === $hash) {
			$redis->del([
				sprintf('meta_%s', $shortUrl),
				sprintf('url_%s', $shortUrl),
				sprintf('num_%s_all', $shortUrl)
			]);

			$keys = $redis->keys(sprintf('num_%s_*', $shortUrl));

			if (count($keys) > 0) {
				$redis->del($keys);
			}
			
			return $app->json(['success' => true], 200);
		}
	}

	return $app->json(['error' => 'Access code is invalid.'], 403);

});