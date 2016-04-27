<?php
$I = new ApiTester($scenario);
$I->wantTo('Fail to create a new short url via API because my authorisation is invalid.');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('url', ['shortUrl' => 'cc_test_' . rand(0,9999999), 'target_url' => 'http://example.com/cc_test', 'access_code' => 'INVALID']);
$I->seeResponseCodeIs(403);
$I->seeResponseIsJson();
$I->seeResponseContains('{"error":"Access code is invalid."}');
