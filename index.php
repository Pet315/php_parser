<?php

require 'vendor/autoload.php';
use Goutte\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

function parsePage($crawler)
{
    $headers = [];

    $crawler->filter('h2')->each(function ($node) use (&$headers) {
        $headers[] = $node->text();
    });

    return [
        'headers' => $headers
    ];
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

}

getData($logger);

exit();
