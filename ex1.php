<?php

require 'vendor/autoload.php'; // Загружаем Goutte
use Goutte\Client;
include 'urls.php';

$url = $ex_url;

$client = new Client();

$crawler = $client->request('GET', $url);

$crawler->filter('h2')->each(function ($node) {
    echo $node->text() . "\n";
});
