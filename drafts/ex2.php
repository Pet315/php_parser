<?php

require 'vendor/autoload.php'; // Загружаем Goutte
use Goutte\Client;
include 'urls.php';

$url = $ex_url; // Замените на URL вашего целевого веб-сайта

$client = new Client();

// Функция для обработки текущей страницы
function parsePage($crawler) {
    $crawler->filter('h1')->each(function ($node) {
//        if ($node->text() !== 'Недопустимое название') {
//            echo $node->text() . "\n";
//        }
        echo $node->text() . "\n";
    });
}

// Запускаем проход по каждой странице
$crawler = $client->request('GET', $url);

// Шаг 1: Переходим на страницу
parsePage($crawler);

// Шаг 2: Переходим на каждую из ссылок внутри таблицы
$crawler->filter('footer a')->each(function ($link) use ($client, &$url) {
    // Получаем URL из атрибута href ссылки
    $nextPageUrl = $link->attr('href');

    // Проверяем, что URL абсолютный
    if (filter_var($nextPageUrl, FILTER_VALIDATE_URL) === false) {
        // Если URL относительный, создаем абсолютный URL
        $nextPageUrl = rtrim($url, '/') . 'ex2.php/' . ltrim($nextPageUrl, '/');
    }

    // Переходим на следующую страницу
    $nextPageCrawler = $client->request('GET', $nextPageUrl);

    // Шаг 3: Обрабатываем текущую страницу
    parsePage($nextPageCrawler);

    // Обновляем URL для следующей итерации
    $url = $nextPageUrl;
});
