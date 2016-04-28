<?php 
$I = new ApiTester($scenario);
$I->wantTo('Delete a short url via API');

$I->amGoingTo('first crate a new short URL');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

$short       = 'cc_test_' . rand(0,9999999);
$url        = 'http://example.com/cc_test';
$domain     = 'api-shortener.lan';
$ac         = 'ui8ujhinijuhiizuhjuko4iuouhsuirehfo7sahuhyfa78z3rsy';

$I->sendPOST('/url/' . $domain, [
	'shortUrl'    => $short, 
	'target_url'  => $url, 
	'access_code' => $ac
]);

$I->seeResponseCodeIs(200);

$I->amGoingTo('now delete the crated URL');

$I->sendDELETE(sprintf('/url/%s/%s?access_code=%s', $domain, $short, $ac));
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"success":true}');

$I->amGoingTo('verify that this short url no longer exsists.');

$I->sendGET(sprintf('/url/%s/%s', $domain, $short));
$I->seeResponseCodeIs(404);