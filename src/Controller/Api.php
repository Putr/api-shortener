<?php

namespace Controller;

use Symfony\Component\HttpFoundation\Request;

class Api extends Base {

	/**
	 * Creates a new short url
	 * @param  Silex\Application $app     
	 * @param  Request           $request 
	 * @param  string            $domain  
	 * @return Response
	 */
	public function createNewShortUrl(\Silex\Application $app, Request $request, $domain) {
		$ac       = $request->get('access_code');
		$shortUrl = $request->get('shortUrl');
		$url      = $request->get('target_url');

		if (empty($ac)) {
			return $app->json(['error' => 'Missing access code'], 400);
		}

		if (empty($shortUrl)) {
			return $app->json(['error' => 'Missing short URL (shortUrl)'], 400);
		}

		if (empty($url)) {
			return $app->json(['error' => 'Missing target url'], 400);
		}

		if (!array_key_exists($domain, $app['config']['domains'])) {
			return $app->json(['error' => 'Domain not enabled.'], 400);
		}

		if ($app['redis']->get(sprintf('%s_url_%s', $domain, $shortUrl)) !== NULL) {
			return $app->json(['error' => 'Short Url already exsists.'], 400);
		}

		//
		// Check for authentication/authorization
		//
		if (!$AcInfo = $this->isAccessCodeValid($app, $ac)) {
			return $app->json(['error' => 'Access code is invalid.'], 403);
		}

		if (!array_search($domain, $AcInfo['enabled_domains'])) {
			return $app->json(['error' => 'Not authorized for this domain.'], 403);
		}

		//
		// Build URL if needed
		//
		if (isset($app['config']['domains'][$app['domain']]['extra_params'])) {
	        $extra = $app['config']['domains'][$app['domain']]['extra_params'];

	        $extra = str_replace('{short-domain}', $app['domain'], $extra);
	        $extra = str_replace('{short-path}', $shortUrl, $extra);

	        $urlMeta = parse_url($url);
	        if (empty($urlMeta['path'])) {
	        	$urlMeta['path'] = '';
	        }
	        if (empty($urlMeta['query'])) {
	        	$urlMeta['query'] = '';
	        }

	        $getParts = explode('&', $urlMeta['query']);
	        $getParts = array_filter($getParts);
	        $extraParts = explode('&', $extra);
	        $extraParts = array_filter($extraParts);

	        $query = array_merge($getParts, $extraParts);
	        $query = implode('&', $query);

	        $url = sprintf('%s://%s%s?%s', $urlMeta['scheme'], $urlMeta['host'], $urlMeta['path'], $query);
	    }

	    //
	    // Save to DB
	    //
		$app['redis']->set(sprintf('%s_url_%s', $domain, $shortUrl), $url);
		$app['redis']->set(sprintf('%s_meta_%s', $domain, $shortUrl), json_encode(
				['creator'   => $AcInfo['label'],
				 'timestamp' => time()
				])
		);
		return $app->json(['success' => true], 200);
	}

	/**
	 * Show details about a short URL
	 * @param  Silex\Application $app      
	 * @param  Request           $request  
	 * @param  string            $domain   
	 * @param  string            $shortUrl 
	 * @return Response
	 */
	public function showShortUrl(\Silex\Application $app, Request $request, $domain, $shortUrl) {
		$meta = $this->getMeta($domain, $shortUrl, $app);

		if (!$meta) {
			return $app->json(['error' => 'Short URL not found.'], 404);
		}

		$url  = $app['redis']->get(sprintf('%s_url_%s', $domain, $shortUrl));

		$numAll = (integer) $app['redis']->get(sprintf('%s_num_%s_all', $domain, $shortUrl));
		$numToday = (integer) $app['redis']->get(sprintf('%s_num_%s_%s', $domain, $shortUrl, date('Ymd')));

		$today = (integer) date('Ymd');
		$numWeek = $numMonth = $numToday;

		for ($i=1; $i < 31; $i++) { 
			$thisDay = $app['redis']->get(sprintf('%s_num_%s_%s', $domain, $shortUrl, $today - $i));

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
			'creator'     => $meta['creator'],
			'timestamp'   => $meta['timestamp'],
			'target_url'  => $url,
			'hits_today'  => $numToday,
			'hits_7days'  => $numWeek,
			'hits_30days' => $numMonth,
			'hits_all'    => $numAll
		];

		return $app->json($payload, 200);
	}

	/**
	 * Removes short url
	 * @param  Silex\Application $app      
	 * @param  Request           $request  
	 * @param  string            $domain   
	 * @param  string            $shortUrl 
	 * @return Response
	 */
	public function deleteShortUrl(\Silex\Application $app, Request $request, $domain, $shortUrl) {
	    $ac = $request->get('access_code');

	    if (empty($ac)) {
			return $app->json(['error' => 'Missing access code'], 400);
		}

		$meta = $this->getMeta($domain, $shortUrl, $app);

		if (!$AcInfo = $this->isAccessCodeValid($app, $ac)) {
			return $app->json(['error' => 'Access code is invalid.'], 403);
		}

		if ($AcInfo['label'] !== $meta['creator']) {
			return $app->json(['error' => 'Can not delete a short URL you are not the creator of.']);
		}

		$app['redis']->del([
			sprintf('%s_meta_%s', $domain, $shortUrl),
			sprintf('%s_url_%s', $domain, $shortUrl),
			sprintf('%s_num_%s_all', $domain, $shortUrl)
		]);

		$keys = $app['redis']->keys(sprintf('%s_num_%s_*', $domain, $shortUrl));

		if (count($keys) > 0) {
			$app['redis']->del($keys);
		}
		
		return $app->json(['success' => true], 200);
		
	}

}



