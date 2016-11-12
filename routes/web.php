<?php

$app->get('/quotes', 'QuoteController@getQuote');

$app->get('/webhook', 'WebHookController@receive');
$app->post('/webhook', 'WebHookController@receive');
