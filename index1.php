<?php

require 'vendor/autoload.php';
use Goutte\Client;

$client = new Client();

function parseLinks($crawler)
{
    foreach ($crawler->filter('.dnrg li a') as $link) {
        echo $link->attr('href') . "\n";
    }
}

function parsePage2($crawler)
{
    foreach ($crawler->filter('.Answer a') as $node) {
        echo $node->text() . "\n";
    }

    foreach ($crawler->filter('tbody .Length') as $node) {
        echo $node->text() . "\n";
    }
}

function processPage($client, $url)
{
    $crawler = $client->request('GET', $url);

    parseLinks($crawler);

    foreach ($crawler->filter('.dnrg li a') as $link) {
        $nextPageUrl = 'https://www.kreuzwort-raetsel.net/' . $link->attr('href');

        if (!filter_var($nextPageUrl, FILTER_VALIDATE_URL)) {
            $nextPageUrl = rtrim($url, '/') . '/' . ltrim($nextPageUrl, '/');
        }

        processPage($client, $nextPageUrl);
    }

    if ($crawler->filter('.Question a')->count()) {
        parsePage2($crawler);
    }
}

// Запускаем проход по каждой странице
$url = 'https://www.kreuzwort-raetsel.net/uebersicht.html';
processPage($client, $url);
