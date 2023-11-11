<?php

require 'vendor/autoload.php'; // Install Goutte
use Goutte\Client;

function parsePage2($crawler) {
    $answers = [];
    $length = [];

    $crawler->filter('.Answer a')->each(function ($node) use (&$answers) {
        $answers[] = $node->text();
    });

    $crawler->filter('tbody .Length')->each(function ($node) use (&$length) {
        $length[] = $node->text();
    });

    return [
        'answers' => $answers,
        'length' => $length
    ];
}

function main($scanner, $client, $url) {
    $letters = [];
    $scanner->filter('.dnrg li a')->each(function ($link) use ($client, &$url, &$letters) {
        $letter = $link->attr('href');
        $nextPageUrl = 'https://www.kreuzwort-raetsel.net/' . $link->attr('href');

        if (filter_var($nextPageUrl, FILTER_VALIDATE_URL) === false) {
            $nextPageUrl = rtrim($url, '/') . '/' . ltrim($nextPageUrl, '/');
        }

        $nextPageScanner = $client->request('GET', $nextPageUrl);
//    parsePage($nextPageCrawler);

        $pages = [];
        $nextPageScanner->filter('.dnrg li a')->each(function ($link) use ($client, &$url1, &$pages) {
            $page = $link->attr('href');
            $nextPageUrl1 = 'https://www.kreuzwort-raetsel.net/' . $link->attr('href');

            if (filter_var($nextPageUrl1, FILTER_VALIDATE_URL) === false) {
                $nextPageUrl1 = rtrim($url1, '/') . '/' . ltrim($nextPageUrl1, '/');
            }

            $nextPageScanner1 = $client->request('GET', $nextPageUrl1);
//        parsePage1($nextPageCrawler1);

            $questions = [];
            $nextPageScanner1->filter('.Question a')->each(function ($link) use ($client, &$url2, &$questions) {
                $question = $link->text();
                $nextPageUrl2 = 'https://www.kreuzwort-raetsel.net/' . $link->attr('href');

                if (filter_var($nextPageUrl2, FILTER_VALIDATE_URL) === false) {
                    $nextPageUrl2 = rtrim($url2, '/') . '/' . ltrim($nextPageUrl2, '/');
                }

                $nextPageScanner2 = $client->request('GET', $nextPageUrl2);
                $answersLength = parsePage2($nextPageScanner2);

                $url2 = $nextPageUrl2;
                $questions[] = [
                    'question' => $question,
                    'answers' => $answersLength['answers'],
                    'length' => $answersLength['length']
                ];
            });

            $url1 = $nextPageUrl1;
            $pages[] = [
                'page' => $page,
                'questions' => $questions
            ];
            move_to_log($pages);
            echo 'just sent';
        });

        $url = $nextPageUrl;
        $letters[] = [
            'letter' => $letter,
            'pages' => $pages
        ];
    });
    move_to_log($letters);
}

function move_to_log($data) {
    file_put_contents('data.log', json_encode($data, JSON_PRETTY_PRINT)."\n", FILE_APPEND);
}

function get_data() {
    $logContent = file_get_contents('data.log');
    return json_decode($logContent, true, 512, JSON_THROW_ON_ERROR);
}

$url = 'https://www.kreuzwort-raetsel.net/uebersicht.html';
$client = new Client();
$scanner = $client->request('GET', $url);

//main($scanner, $client, $url);
print_r(get_data()[0]['questions'][0]);
