<?php 
$I = new ApiTester($scenario);
$I->wantTo('create a short url via API');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

$short = 'cc_test_' . rand(0,9999999);
$domain = 'api-shortener.lan';
$ac = 'ui8ujhinijuhiizuhjuko4iuouhsuirehfo7sahuhyfa78z3rsy';
$target_url = 'http://example.com/cc_test';

$I->sendPOST('/url/' . $domain, [
	'shortUrl'    => $short, 
	'target_url'  => $target_url, 
	'access_code' => $ac,
]);

$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('{"success":true}');

$I->amGoingTo('try to create a duplicate/overwrite');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('/url/' . $domain, [
	'shortUrl'    => $short, 
	'target_url'  => $target_url, 
	'access_code' => $ac,
]);
$I->seeResponseCodeIs(400);