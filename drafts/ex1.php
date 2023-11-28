<?php

require 'vendor/autoload.php'; // Загружаем Goutte
use Goutte\Client;
include 'urls.php';

$url = $ex_url;

$proxy = [
    'http' => 'http://34.77.56.122:8080',
    'https' => 'https://34.77.56.122:8080',
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_PROXY, $proxy['http']);
$response = curl_exec($ch);

echo "\nabc\n";

$dom = new DOMDocument();
@$dom->loadHTML($response);

// Получаем все теги h2
$h2s = $dom->getElementsByTagName("h2");

// Выводим все теги h2
foreach ($h2s as $h2) {
    echo $h2->textContent . PHP_EOL;
}

//$data = json_decode($response, true);
//var_dump($ch);

//$h2s = array_filter($data, function ($element) {
//    return $element['tagName'] === 'h2';
//});
//foreach ($h2s as $h2) {
//    echo $h2['textContent'];
//}

//$client = new Client();
//$crawler = $client->request('GET', $response);
//$crawler->filter('h2')->each(function ($node) {
//    echo $node->text() . "\n";
//});
