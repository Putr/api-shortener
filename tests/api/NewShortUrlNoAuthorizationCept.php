<?php
$I = new ApiTester($scenario);
$I->wantTo('Fail to create a new short url via API because my authorisation is invalid.');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

$short = 'cc_test_' . rand(0,9999999);
$domain = 'api-shortener.lan';
$ac = 'INVALID';
$target_url = 'http://example.com/cc_test';

$I->sendPOST('/url/' . $domain, [
	'shortUrl'    => $short, 
	'target_url'  => $target_url, 
	'access_code' => $ac
]);

$I->seeResponseCodeIs(403);
$I->seeResponseIsJson();
$I->seeResponseContains('{"error":"Access code is invalid."}');
