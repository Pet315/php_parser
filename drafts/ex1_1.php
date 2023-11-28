<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
include 'urls.php';

$url = $ex_url;

// Создаем объект клиента Guzzle с указанием прокси
$proxy = 'http://34.77.56.122:8080'; // Укажите свои значения IP и порта прокси-сервера
$client = new Client([
    'base_uri' => $url,
    'proxy' => $proxy,
    'timeout' => 10,
]);

try {
    // Выполнение запроса через прокси
    $response = $client->request('GET', '/path/to/resource');

    // Получение данных из ответа
    $body = $response->getBody()->getContents();

    // Дальнейшая обработка данных...
    echo $body;
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage();
}

