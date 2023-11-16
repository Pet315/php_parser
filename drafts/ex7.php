<?php


require 'vendor/autoload.php';

use Goutte\Client;

class SimpleParser
{
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function parsePage($url)
    {
        $crawler = $this->client->request('GET', $url);

        $h1Elements = $this->extractH1($crawler);

        return $h1Elements;
    }

    private function extractH1($crawler)
    {
        $h1Elements = $crawler->filter('h2')->each(function ($node) {
            return $node->text();
        });

        return $h1Elements;
    }
}

$parser = new SimpleParser();
$url = 'https://ru.wikipedia.org/wiki/Чудеса_света';
$result = $parser->parsePage($url);

foreach ($result as $h1) {
    echo $h1 . "\n";
}
