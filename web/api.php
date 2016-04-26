<?php

$app->post('/api/v1/url', function (Silex\Application $app, Request $request) use ($redis) {
	$ac = $request->get('access_code');
	$slug = $request->get('slug');
	$url = $request->get('target_url');


});

$app->get('/api/v1/url/{slug}', function (Silex\Application $app, Request $request, $slug) use ($redis) {
	
});

$app->delete('/api/v1/url/{slug}', function (Silex\Application $app, Request $request, $slug) use ($redis) {
    $ac = $request->get('access_code');

});