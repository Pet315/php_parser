<?php

require 'vendor/autoload.php'; // Install Goutte
use Goutte\Client;

$url = 'https://www.kreuzwort-raetsel.net/uebersicht.html';

$client = new Client();

function parsePage($crawler) {
    $crawler->filter('.dnrg li a')->each(function ($node) {
        echo $node->attr('href') . "\n";
    });
}

//function parsePage1($crawler) {
//    $crawler->filter('.Question a')->each(function ($node) {
//        echo $node->text() . "\n";
//    });
//}

function parsePage2($crawler) {
    $crawler->filter('.Answer a')->each(function ($node) {
        echo $node->text() . "\n";
    });
    $crawler->filter('tbody .Length')->each(function ($node) {
        echo $node->text() . "\n";
    });
//    $crawler->filter('tr')->each(function ($node) {
//        echo $node->filter('.Answer a')->text() . ': ' . $node->filter('.Length a')->text() . "\n";
//    });
}

//function parsePageEx($crawler) {
//    $crawler->filter('h2')->each(function ($node) {
//        echo $node->text() . "\n";
//    });
//}

// Запускаем проход по каждой странице
$crawler = $client->request('GET', $url);

// Шаг 1: Переходим на страницу
parsePage($crawler);

// Шаг 2: Переходим на каждую из ссылок внутри таблицы
$crawler->filter('.dnrg li a')->each(function ($link) use ($client, &$url) {
    $nextPageUrl = 'https://www.kreuzwort-raetsel.net/' . $link->attr('href');

    if (filter_var($nextPageUrl, FILTER_VALIDATE_URL) === false) {
        $nextPageUrl = rtrim($url, '/') . '/' . ltrim($nextPageUrl, '/');
    }

    $nextPageCrawler = $client->request('GET', $nextPageUrl);
//    parsePage($nextPageCrawler);

    $nextPageCrawler->filter('.dnrg li a')->each(function ($link) use ($client, &$url1) {
        echo $link->attr('href') . "\n";
        $nextPageUrl1 = 'https://www.kreuzwort-raetsel.net/' . $link->attr('href');

        if (filter_var($nextPageUrl1, FILTER_VALIDATE_URL) === false) {
            $nextPageUrl1 = rtrim($url1, '/') . '/' . ltrim($nextPageUrl1, '/');
        }

        $nextPageCrawler1 = $client->request('GET', $nextPageUrl1);
//        parsePage1($nextPageCrawler1);

        $nextPageCrawler1->filter('.Question a')->each(function ($link) use ($client, &$url2) {
            echo $link->text() . "\n";
            $nextPageUrl2 = 'https://www.kreuzwort-raetsel.net/' . $link->attr('href');

            if (filter_var($nextPageUrl2, FILTER_VALIDATE_URL) === false) {
                $nextPageUrl2 = rtrim($url2, '/') . '/' . ltrim($nextPageUrl2, '/');
            }

            $nextPageCrawler2 = $client->request('GET', $nextPageUrl2);
            parsePage2($nextPageCrawler2);
            $url2 = $nextPageUrl2;
        });

        $url1 = $nextPageUrl1;
    });

    $url = $nextPageUrl;
});
