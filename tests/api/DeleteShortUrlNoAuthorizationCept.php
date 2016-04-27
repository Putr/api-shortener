<?php 
$I = new ApiTester($scenario);
$I->wantTo('Fail to delete a short url via API because of invalid authorisation');

$I->amGoingTo('first crate a new short URL');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

$ac = 'ui8ujhinijuhiizuhjuko4iuouhsuirehfo7sahuhyfa78z3rsy';
$slug  = 'cc_test_' . rand(0,9999999);
$url   = 'http://example.com/cc_test';
$I->sendPOST('url', ['shortUrl' => $slug, 'target_url' => $url, 'access_code' => $ac]);
$I->seeResponseCodeIs(200);

$I->amGoingTo('now delete the crated URL');

$I->sendDELETE(sprintf('url/%s?access_code=%s', $slug, 'INVALID_AC'));
$I->seeResponseCodeIs(403);

$I->amGoingTo('verify that this short url still exsists.');

$I->sendGET('url/' . $slug);
$I->seeResponseCodeIs(200);