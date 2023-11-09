<?php

require 'vendor/autoload.php'; // Загружаем Goutte
use Goutte\Client;
include 'urls.php';

$url = $url1; // Замените на URL вашего целевого веб-сайта

$client = new Client();

// Функция для обработки текущей страницы
function parsePage($crawler) {
    $crawler->filter('h2')->each(function ($node) {
//        if ($node->text() !== 'Seite wurde nicht gefunden') {
//            echo $node->text() . "\n";
//        }
        echo $node->text() . "\n";
    });
}

function parsePage1($crawler) {
    $crawler->filter('li a')->each(function ($node) {
        echo $node->attr('href') . "\n";
    });
}

// Запускаем проход по каждой странице
$crawler = $client->request('GET', $url);

// Шаг 1: Переходим на страницу
parsePage($crawler);

// Шаг 2: Переходим на каждую из ссылок внутри таблицы
$crawler->filter('li a')->each(function ($link) use ($client, &$url) {
    // Получаем URL из атрибута href ссылки
    $nextPageUrl = 'https://www.kreuzwort-raetsel.net/' . $link->attr('href');

    // Проверяем, что URL абсолютный
    if (filter_var($nextPageUrl, FILTER_VALIDATE_URL) === false) {
        // Если URL относительный, создаем абсолютный URL
        $nextPageUrl = rtrim($url, '/') . '/' . ltrim($nextPageUrl, '/');
    }

    // Переходим на следующую страницу
    $nextPageCrawler = $client->request('GET', $nextPageUrl);

    // Шаг 3: Обрабатываем текущую страницу
    parsePage($nextPageCrawler);

    // Обновляем URL для следующей итерации
    $url = $nextPageUrl;
});