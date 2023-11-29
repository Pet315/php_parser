<?php

require 'vendor/autoload.php';

function find_text_elements($html, $tag='h2') {
    $doc = new DOMDocument();
    $doc->loadHTML($html);

    $elements = $doc->getElementsByTagName($tag);
    $data = [];

    foreach ($elements as $element) {
        $data[] = $element->textContent . PHP_EOL;
    }

    return $data;
}

function find_href($html, $tag='a') {
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    $elements = $doc->getElementsByTagName($tag);

    $data = [];
    foreach ($elements as $element) {
        $data[] = $element->getAttribute("href");
    }

    return $data;
}

function run($url, $proxy='http://34.77.56.122:8080', $filename='output1.txt') {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_PROXY, $proxy);

    ob_start();
    curl_exec($ch);
    curl_close($ch);
    $contents = ob_get_contents();
    file_put_contents($filename, $contents);
    ob_end_clean();

    $file = fopen($filename, "r");
    $contents = fread($file, filesize($filename));
    fclose($file);

    return $contents;
}

$url = 'https://www.kreuzwort-raetsel.net/';
$contents = run($url.'uebersicht.html');

$letters = find_href($contents);
$links = [];
foreach ($letters as $letter) {
    $letter = trim($letter);
    if ((strlen($letter) === 1 or $letter === 'sonstige') and $letter !== '#') {
        $links[] = $letter;
    }
}

foreach ($links as $link) {
    $contents = run($url.$link);
    $pages = find_href($contents);
    $links2 = [];

    foreach ($pages as $page) {
        $page = trim($page);
        if (ctype_alpha($page[0]) and $page[1] === "-") {
            for ($i = 2, $iMax = strlen($page); $i < $iMax; $i++) {
                if (!ctype_digit($page[$i])) {
                    continue;
                }
                $links2[] = $page;
            }
        }
    }
    print_r($links2);
}

//$client = new Client();
//$contents = $client->request('GET', $url);
//file_put_contents('output1.txt', $contents);
