<?php 
$I = new ApiTester($scenario);
$I->wantTo('Get information about a short url via API');

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

$I->amGoingTo('check information about the url');

$I->sendGET(sprintf('/url/%s/%s', $domain, $short));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseMatchesJsonType([
	'creator'     => 'string',
	'timestamp'   => 'integer',
	'target_url'  => 'string:url',
	'hits_today'  => 'integer',
	'hits_7days'   => 'integer',
	'hits_30days' => 'integer',
	'hits_all'    => 'integer'
]);
