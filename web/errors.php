<?php

$app->get('/error/{shortUrl}', function (Silex\Application $app, Request $request, $shortUrl) {
    return "This short URL does not exsist.";
});

$app->get('/error/invalid-domain', function (Silex\Application $app, Request $request) {
    return "This domain is not enabled.";
});