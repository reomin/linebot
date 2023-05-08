<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

$httpClient = new CurlHTTPClient($_ENV['LINE_CHANNEL_ACCESS_TOKEN']);
$bot = new LINEBot($httpClient, ['channelSecret' => $_ENV['LINE_CHANNEL_SECRET']]);

Route::post('/webhook', function (Request $request) use ($bot) {
    $request->collect('events')->each(function ($event) use ($bot) {
        $bot->replyText($event['replyToken'], $event['message']['text']);
    });
    return 'ok!';
});
