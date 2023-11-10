<?php

require 'vendor/autoload.php'; // Загружаем Goutte
use Goutte\Client;
include 'urls.php';

// Замените настройки прокси на реальные
$proxyHost = '35.209.198.222';
$proxyPort = '80';
$proxyUsername = ''; // Установите логин, если прокси требует аутентификации
$proxyPassword = ''; // Установите пароль, если прокси требует аутентификации

// URL для парсинга
$url = $ex_url;

// Создаем клиент Goutte
$client = new Client();

// Устанавливаем прокси-сервер
$proxyUrl = "http://$proxyHost:$proxyPort";
$client->getClient()->setDefaultOption('proxy', $proxyUrl);

// Если требуется аутентификация на прокси
if (!empty($proxyUsername) && !empty($proxyPassword)) {
    $client->getClient()->setDefaultOption('proxy_headers', [
        'Proxy-Authorization' => 'Basic ' . base64_encode("$proxyUsername:$proxyPassword"),
    ]);
}

// Функция для обработки текущей страницы
function parsePage($crawler) {
    $crawler->filter('h1')->each(function ($node) {
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
        $nextPageUrl = rtrim($url, '/') . 'ex3.php/' . ltrim($nextPageUrl, '/');
    }

    // Переходим на следующую страницу
    $nextPageCrawler = $client->request('GET', $nextPageUrl);

    // Шаг 3: Обрабатываем текущую страницу
    parsePage($nextPageCrawler);

    // Обновляем URL для следующей итерации
    $url = $nextPageUrl;
});
