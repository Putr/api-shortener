<?php 
$I = new ApiTester($scenario);
$I->wantTo('create a short url via API');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$short = 'cc_test_' . rand(0,9999999);
$I->sendPOST('url', ['shortUrl' => $short, 'target_url' => 'http://example.com/cc_test', 'access_code' => 'ui8ujhinijuhiizuhjuko4iuouhsuirehfo7sahuhyfa78z3rsy']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('{"success":true}');

$I->amGoingTo('try to create a duplicate/overwrite');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('url', ['shortUrl' => $short, 'target_url' => 'http://example.com/cc_test', 'access_code' => 'ui8ujhinijuhiizuhjuko4iuouhsuirehfo7sahuhyfa78z3rsy']);
$I->seeResponseCodeIs(400);
