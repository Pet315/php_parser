<?php

require 'vendor/autoload.php';
use Goutte\Client;

function parseLinks($crawler) {
    $crawler->filter('.dnrg li a')->each(function ($link) {
        echo $link->attr('href') . "\n";
    });
}

function parsePage2($crawler) {
    $crawler->filter('.Answer a')->each(function ($node) {
        echo $node->text() . "\n";
    });

    $crawler->filter('tbody .Length')->each(function ($node) {
        echo $node->text() . "\n";
    });
}

function processPage($client, &$url) {
    $crawler = $client->request('GET', $url);
    parseLinks($crawler);

    $crawler->filter('.dnrg li a')->each(function ($link) use ($client, &$url) {
        $nextPageUrl = 'https://www.kreuzwort-raetsel.net/' . $link->attr('href');

        if (filter_var($nextPageUrl, FILTER_VALIDATE_URL) === false) {
            $nextPageUrl = rtrim($url, '/') . 'ex5.php/' . ltrim($nextPageUrl, '/');
        }

        processPageRecursively($client, $nextPageUrl);
    });
}

function processPageRecursively($client, &$url) {
    $crawler = $client->request('GET', $url);
    parseLinks($crawler);

    $crawler->filter('.dnrg li a')->each(function ($link) use ($client, &$url) {
        $nextPageUrl = 'https://www.kreuzwort-raetsel.net/' . $link->attr('href');

        if (filter_var($nextPageUrl, FILTER_VALIDATE_URL) === false) {
            $nextPageUrl = rtrim($url, '/') . 'ex5.php/' . ltrim($nextPageUrl, '/');
        }

        processPageRecursively($client, $nextPageUrl);
    });

    $crawler->filter('.Question a')->each(function ($link) use ($client) {
        echo $link->text() . "\n";
        $nextPageUrl = 'https://www.kreuzwort-raetsel.net/' . $link->attr('href');

        if (filter_var($nextPageUrl, FILTER_VALIDATE_URL) === false) {
            $nextPageUrl = rtrim($url, '/') . 'ex5.php/' . ltrim($nextPageUrl, '/');
        }

        $nextPageCrawler = $client->request('GET', $nextPageUrl);
        parsePage2($nextPageCrawler);
    });
}

// Запускаем проход по каждой странице
$url = 'https://www.kreuzwort-raetsel.net/uebersicht.html';
$client = new Client();
processPage($client, $url);
