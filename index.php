<?php

require 'vendor/autoload.php';
use Goutte\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

function parsePage($crawler)
{
    $answers = [];
    $answers_length = [];

    $crawler->filter('.Answer a')->each(function ($node) use (&$answers) {
        $answers[] = $node->text();
    });

    $crawler->filter('tbody .Length')->each(function ($node) use (&$answers_length, &$answers) {
        $i = count($answers_length);
        $answers_length[] = [
            'answer' => $answers[$i],
            'length' => $node->text()
        ];
    });

    return $answers_length;
}

function run($nextPageUrl, $header) {
    $logger = new Logger('logger');

    $streamHandler = new StreamHandler('logs/data.log', Logger::INFO);
    $logger->pushHandler($streamHandler);
    $client = new Client();
    $nextPageCrawler = $client->request('GET', $nextPageUrl);

    $content = parsePage($nextPageCrawler);
    $logger->addRecord(
        Logger::INFO,
        'Page link',
        [$nextPageUrl]
    );

    return [
        'question' => $header,
        'answer_length' => $content
    ];
}

function connect_to_db($crossword) {
    foreach ($crossword['answer_length'] as $word) {
        $db = new PDO('mysql:host=localhost;dbname=php_parser', 'root', '12345678');
        $sql = "SELECT COUNT(*) AS count FROM crossword WHERE question = :question AND answer = :answer;";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':question', $crossword['question']);
        $stmt->bindParam(':answer', $word['answer']);
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result['count'] === 0) {
            try {
                $sql = "INSERT INTO crossword (question, answer, length) VALUES (:question, :answer, :length)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':question', $crossword['question']);
                $stmt->bindParam(':answer', $word['answer']);
                $stmt->bindParam(':length', $word['length']);
                $stmt->execute();
            } catch (PDOException $e) {
                $logger1 = new Logger('logger');
                $streamHandler = new StreamHandler('logs/debug.log', Logger::DEBUG);
                $logger1->pushHandler($streamHandler);
                $logger1->debug("Error: " . $e->getMessage());
            }
        }
    }
    return "next page\n";
}

$client = new Client();
$url = 'https://www.kreuzwort-raetsel.net/uebersicht.html';
$crawler = $client->request('GET', $url);

$nextPageUrl = '';
$crawler->filter('.dnrg li a')->each(function ($link) use ($client, &$nextPageUrl) {
    $letter = $link->attr('href');
    $nextPageUrl = 'https://www.kreuzwort-raetsel.net/' . $letter;
    $crawler2 = $client->request('GET', $nextPageUrl);

    $nextPageUrl2 = '';
    $crawler2->filter('.dnrg li a')->each(function ($link) use ($client, &$nextPageUrl2) {
        $nextPageUrl2 = 'https://www.kreuzwort-raetsel.net/' . $link->attr('href');
        $crawler3 = $client->request('GET', $nextPageUrl2);

        $nextPageUrl3 = '';
        $crawler3->filter('.Question a')->each(function ($link) use (&$nextPageUrl3) {
            $nextPageUrl3 = 'https://www.kreuzwort-raetsel.net/' . $link->attr('href');
            connect_to_db(run($nextPageUrl3, $link->text()));
        });
        echo $nextPageUrl3;
    });
    echo $nextPageUrl2;
});
echo $nextPageUrl;
