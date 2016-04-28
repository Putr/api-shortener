<?php

namespace Controller;

use Symfony\Component\HttpFoundation\Request;

class Error {

	public function missingShortUrl(\Silex\Application $app, Request $request, $shortUrl) {
	    return "This short URL does not exsist.";
	}

	public function domainNotEnabled(\Silex\Application $app, Request $request) {
	    return "This domain is not enabled.";
	}

}