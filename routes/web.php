<?php

$app->get('/adminer', 'AdminerController@adminer');
$app->post('/adminer', 'AdminerController@adminer');
$app->put('/adminer', 'AdminerController@adminer');
$app->patch('/adminer', 'AdminerController@adminer');
$app->options('/adminer', 'AdminerController@adminer');
$app->delete('/adminer', 'AdminerController@adminer');

$app->get('/quotes', 'QuoteController@getQuote');
$app->post('/quotes', 'QuoteController@getQuote');

$app->get('/quotes/{quoteId}', 'QuoteController@show');

$app->get('/webhook', 'WebHookController@receive');
$app->post('/webhook', 'WebHookController@receive');

$app->get('/payment', 'PaymentControlller@pay');
$app->post('/payment', 'PaymentControlller@pay');
