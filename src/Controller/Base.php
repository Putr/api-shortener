<?php

namespace Controller;

use Symfony\Component\HttpFoundation\Request;

class Base {

	/**
	 * Validates access code against those stored in config
	 * @param  \Silex\Application  $app
	 * @param  string  			   $accessCode
	 * @return boolean|array
	 */
	public function isAccessCodeValid($app, $accessCode) {
		$acHash = sha1($accessCode);
		$pass = false;
		foreach ($app['config']['access_codes'] as $label => $c) {
			if ($acHash === $c['secret']) {
				$pass = true;
				break;
			}
		}

		if ($pass) {
			$c['label'] = $label;
			return $c;
		}

		return false;
	}

	/**
	 * Retrives and decodes meta informaton
	 * @param  string $domain
	 * @param  string $shortUrl
	 * @param  \Silex\Application $app
	 * @return boolean|array
	 */
	public function getMeta($domain, $shortUrl, $app) {
		$meta = $app['redis']->get(sprintf('%s_meta_%s', $domain, $shortUrl));

		if ($meta === null) {
			return false;
		}

		$meta = json_decode($meta, true);
		return $meta;
	}
}