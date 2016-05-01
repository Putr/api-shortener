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
}