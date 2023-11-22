<?php

require 'vendor/autoload.php';
use Goutte\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

function parsePage($crawler, $text='.dnrg li')
{
    $links = [];

    $crawler->filter($text.' a')->each(function ($node) use (&$links) {
        $links[] = $node->text();
    });

    return [
        'links' => $links
    ];
}

function run($nextPageUrl, $number=1) {
    $logger = new Logger('logger');
    $streamHandler = new StreamHandler('logs/log'.$number.'.log', Logger::INFO);
    $logger->pushHandler($streamHandler);
    $client = new Client();
    $nextPageCrawler = $client->request('GET', $nextPageUrl);
    $data = parsePage($nextPageCrawler);

    $logger->addRecord(
        Logger::INFO,
        'Page analysis',
        ['url' => $nextPageUrl, 'data' => $data]
    );

    return $nextPageUrl;
}

function getData($logger) {
    $logger->info('Printing logger data:');
    foreach ($logger->getHandlers() as $handler) {

        $url = $handler->getUrl();
        $fp = fopen($url, 'r');
        $data = fread($fp, filesize($url));
        fclose($fp);

        $messages = explode("\n", $data);
        $objects = [];

        foreach ($messages as $message) {
            $startIndex = strpos($message, "{");
            if ($startIndex) {
                $extractedData = substr($message, $startIndex);
                $objects[] = json_decode(substr($extractedData, 0, -2));
            }
        }

        print_r($objects);
//    print_r($objects[0]->data->headers[0]);
    }
}

$client = new Client();
$url = 'https://www.kreuzwort-raetsel.net/uebersicht.html';
$crawler = $client->request('GET', $url);

$crawler->filter('.dnrg li a')->each(function ($link) use ($client) {
    $letter = $link->attr('href');
    $nextPageUrl = 'https://www.kreuzwort-raetsel.net/' . $letter;

    $pid1 = pcntl_fork();
    if ($pid1 === 0) {
        $nextPageUrl = run($nextPageUrl);

        $crawler2 = $client->request('GET', $nextPageUrl);
        $crawler2->filter('.dnrg li a')->each(function ($link) {
            $page = $link->attr('href');
            $nextPageUrl = 'https://www.kreuzwort-raetsel.net/' . $page;
            $pid2 = pcntl_fork();
            if ($pid2 === 0) {
                $nextPageUrl = run($nextPageUrl, 2);
            }
        });

        exit();
    }
});

while (pcntl_wait($status) !== -1) {
}

//getData($logger);

exit();
