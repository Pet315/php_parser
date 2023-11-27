<?php

require 'vendor/autoload.php';
use Goutte\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

function parsePage($crawler, $tag)
{
    if ($tag) {
        $data = [];
        $crawler->filter($tag)->each(function ($node) use (&$data) {
            $data[] = $node->text();
        });
        return $data;
    }

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

function run($nextPageUrl, $message, $number=1, $tag='.dnrg li a') {
    $logger = new Logger('logger');
    $streamHandler = new StreamHandler('logs/log'.$number.'.log', Logger::INFO);
    $logger->pushHandler($streamHandler);
    $client = new Client();
    $nextPageCrawler = $client->request('GET', $nextPageUrl);
    $data = parsePage($nextPageCrawler, $tag);

    $logger->addRecord(
        Logger::INFO,
        $message,
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

//    $pid1 = pcntl_fork();
//    if ($pid1 === 0) {
//        run($nextPageUrl, 'Letters-Pages');

        $crawler2 = $client->request('GET', $nextPageUrl);
        $crawler2->filter('.dnrg li a')->each(function ($link) use ($client) {
            $page = $link->attr('href');
            $nextPageUrl = 'https://www.kreuzwort-raetsel.net/' . $page;
//            $pid2 = pcntl_fork();
//            if ($pid2 === 0) {
//                run($nextPageUrl, 'Pages-Questions', 2, '.Question a');
                $crawler3 = $client->request('GET', $nextPageUrl);
                $crawler3->filter('.Question a')->each(function ($link) use ($client) {
                    $page = $link->attr('href');
                    $nextPageUrl = 'https://www.kreuzwort-raetsel.net/' . $page;
//                    $pid3 = pcntl_fork();
//                    if ($pid3 === 0) {
                        run($nextPageUrl, 'Questions-Answers-Length', 3, null);
//                    }
                });
//            }
        });

        exit();
//    }
});

//while (pcntl_wait($status) !== -1) {
//}

//getData($logger);

exit();
