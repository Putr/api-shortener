<?php 
$I = new ApiTester($scenario);
$I->wantTo('Get information about a short url via API');

$I->amGoingTo('first crate a new short URL');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

$slug  = 'cc_test_' . rand(0,9999999);
$url   = 'http://example.com/cc_test';
$label = "tester";
$I->sendPOST('url', ['shortUrl' => $slug, 'target_url' => $url, 'access_code' => 'ui8ujhinijuhiizuhjuko4iuouhsuirehfo7sahuhyfa78z3rsy']);
$I->seeResponseCodeIs(200);

$I->amGoingTo('check information about the url');

$I->sendGET('url/' . $slug);
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
