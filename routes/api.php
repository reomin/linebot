<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use GuzzleHttp\Client;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\Event\JoinEvent;
use App\Http\Controllers\LineBotController;

function getDogImage()
{
    $client = new Client();
    $response = $client->get('https://dog.ceo/api/breeds/image/random');
    $data = json_decode($response->getBody(), true);
    $imageUrl = $data['message'];
    return $imageUrl;
}


$httpClient = new CurlHTTPClient($_ENV['LINE_CHANNEL_ACCESS_TOKEN']);
$bot = new LINEBot($httpClient, ['channelSecret' => $_ENV['LINE_CHANNEL_SECRET']]);

Route::post('/webhook', function (Request $request) use ($bot) {
    //userがなにかactionを起こした問いに発生する

    $events = $bot->parseEventRequest($request->getContent(), $request->header('X-Line-Signature'));
    foreach ($events as $event) {
        if ($event instanceof JoinEvent) {
            $groupId = $event->getGroupId();
            $bot->replyText($event->getReplyToken(), 'こんにちは!!!! reodogと申します。癒しの写真が欲しい時は@reodogと送信ください!!');
        }
    }


    $request->collect('events')->each(function ($event) use ($bot) {
        if ($event['type'] === 'message' && $event['message']['type'] === 'text') {
            // メッセージイベントかつテキストメッセージの場合の処理
            $messageText = $event['message']['text'];
            if (false !== strstr($messageText, '@reodog')) {
                $imageUrl = getDogImage();
                $imageMessage = new ImageMessageBuilder($imageUrl, $imageUrl);
                $bot->replyMessage($event['replyToken'], $imageMessage);
            } else if (false !== strstr($messageText, '@褒めて')) {
                $test = LineBotController::make_prize();
                $test = "{$test}!!!";
                $message = new TextMessageBuilder($test);
                $bot->replyMessage($event['replyToken'], $message);
            }
        }
    });
    return "ok";
});
