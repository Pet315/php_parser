<?php

require 'vendor/autoload.php';
use Goutte\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

function parsePage($crawler)
{
    $answers = [];
    $length = [];

    $crawler->filter('.Answer a')->each(function ($node) use (&$answers) {
        $answers[] = $node->text();
    });

    $crawler->filter('tbody .Length')->each(function ($node) use (&$length) {
        $length[] = $node->text();
    });

    return [
        'answers' => $answers,
        'length' => $length
    ];
}

$logger = new Logger('my_logger');
$streamHandler = new StreamHandler('logs/my_log.log', Logger::INFO);
$logger->pushHandler($streamHandler);

$client = new Client();
$url = 'https://www.kreuzwort-raetsel.net/uebersicht.html';
$crawler = $client->request('GET', $url);

$crawler->filter('.dnrg li a')->each(function ($link) use ($client, $logger) {
    $letter = $link->attr('href');
    $nextPageUrl = 'https://www.kreuzwort-raetsel.net/' . $letter;

    $pid = pcntl_fork();

    if ($pid === 0) {
        $client = new Client();
        $nextPageCrawler = $client->request('GET', $nextPageUrl);
        $data = parsePage($nextPageCrawler);

        $logger->addRecord(
            Logger::INFO,
            'Page analysis',
            ['url' => $nextPageUrl, 'data' => $data]
        );

        exit();
    }
});

while (pcntl_wait($status) != -1) {
    // ...
}

exit();
