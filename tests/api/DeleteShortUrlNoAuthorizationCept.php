<?php 
$I = new ApiTester($scenario);
$I->wantTo('Fail to delete a short url via API because of invalid authorisation');

$I->amGoingTo('first crate a new short URL');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

$short = 'cc_test_' . rand(0,9999999);
$domain = 'api-shortener.lan';
$ac = 'ui8ujhinijuhiizuhjuko4iuouhsuirehfo7sahuhyfa78z3rsy';
$target_url = 'http://example.com/cc_test';

$I->sendPOST('/url/' . $domain, [
	'shortUrl'    => $short, 
	'target_url'  => $target_url, 
	'access_code' => $ac
]);
$I->seeResponseCodeIs(200);

$I->amGoingTo('now delete the crated URL');

$I->sendDELETE(sprintf('/url/%s/%s?access_code=%s', $domain, $short, 'INVALID_AC'));
$I->seeResponseCodeIs(403);

$I->amGoingTo('verify that this short url still exsists.');

$I->sendGET(sprintf('/url/%s/%s', $domain, $short));
$I->seeResponseCodeIs(200);