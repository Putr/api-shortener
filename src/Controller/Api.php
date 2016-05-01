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

		if ($app['model']->getUrl($domain, $shortUrl) !== NULL) {
			return $app->json(['error' => 'Short Url already exsists.'], 400);
		}

		//
		// Check for authentication/authorization
		//
		if (!$AcInfo = $this->isAccessCodeValid($app, $ac)) {
			return $app->json(['error' => 'Access code is invalid.'], 403);
		}

		if (array_search($domain, $AcInfo['enabled_domains']) === false) {
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
		$app['model']->setUrl($domain, $shortUrl, $url);
		$meta = ['creator'   => $AcInfo['label'],
				 'timestamp' => time()
				];
		$app['model']->setMeta($domain, $shortUrl, $meta);

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
		$meta = $app['model']->getMeta($domain, $shortUrl);

		if (!$meta) {
			return $app->json(['error' => 'Short URL not found.'], 404);
		}

		$url  = $app['model']->getUrl($domain, $shortUrl);
		$stats = $app['model']->getStats($domain, $shortUrl);

		$payload = [
			'creator'    => $meta['creator'],
			'timestamp'  => $meta['timestamp'],
			'target_url' => $url,
		];

		$payload = array_merge($payload, $stats);

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

		$meta = $app['model']->getMeta($domain, $shortUrl, $app);

		if (!$AcInfo = $this->isAccessCodeValid($app, $ac)) {
			return $app->json(['error' => 'Access code is invalid.'], 403);
		}

		if ($AcInfo['label'] !== $meta['creator']) {
			return $app->json(['error' => 'Can not delete a short URL you are not the creator of.']);
		}

		$app['model']->removeRecord($domain, $shortUrl);
		
		return $app->json(['success' => true], 200);
		
	}

	/**
	 * Returnes enabled domains for the provided access code
	 * @param  \Silex\Application $app
	 * @return Response
	 */
	public function getAllEnabledDomains(\Silex\Application $app, Request $request) {
		$ac = $request->get('access_code');
		$acHash = sha1($ac);

		foreach ($app['config']['access_codes'] as $label => $params) {
			if ($params['secret'] === $acHash) {
				if (isset($params['enabled_domains'])) {
					return $app->json($params['enabled_domains']);
				} else {
					foreach ($app['config']['domains'] as $domain => $params) {
						$payload[] = $domain;
					}
					return $app->json($payload);
				}
			}
		}

		return $app->json(['error' => 'Access code is invalid.'], 403);
	}

	public function getDomainUrls(\Silex\Application $app, $domain) {
		$payload = $app['model']->getAllRecordsForDomain($domain);

		return $app->json($payload);
	}

}



