<?php 
$I = new ApiTester($scenario);
$I->wantTo('Delete a short url via API');

$I->amGoingTo('first crate a new short URL');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

$ac = 'ui8ujhinijuhiizuhjuko4iuouhsuirehfo7sahuhyfa78z3rsy';
$slug  = 'cc_test_' . rand(0,9999999);
$url   = 'http://example.com/cc_test';
$I->sendPOST('url', ['shortUrl' => $slug, 'target_url' => $url, 'access_code' => $ac]);
$I->seeResponseCodeIs(200);

$I->amGoingTo('now delete the crated URL');

$I->sendDELETE(sprintf('url/%s?access_code=%s', $slug, $ac));
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"success":true}');

$I->amGoingTo('verify that this short url no longer exsists.');

$I->sendGET('url/' . $slug);
$I->seeResponseCodeIs(404);