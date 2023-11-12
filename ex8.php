<?php

require 'vendor/autoload.php';

use Goutte\Client;

class ParallelParser
{
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function parsePages($urls)
    {
        $pids = [];

        foreach ($urls as $url) {
            $pid = pcntl_fork();

            if ($pid == -1) {
                die('Could not fork.');
            } elseif ($pid) {
                // Parent process
                $pids[] = $pid;
            } else {
                // Child process
                $this->parsePage($url);
                exit(0);
            }
        }

        // Wait for all child processes to finish
        foreach ($pids as $pid) {
            pcntl_waitpid($pid, $status);
        }
    }

    private function parsePage($url)
    {
        $crawler = $this->client->request('GET', $url);
        $h1Elements = $this->extractH1($crawler);

        echo "Results for $url:\n";
        foreach ($h1Elements as $h1) {
            echo $h1 . "\n";
        }
    }

    private function extractH1($crawler)
    {
        $h1Elements = $crawler->filter('h2')->each(function ($node) {
            return $node->text();
        });

        return $h1Elements;
    }
}

// Пример использования парсера
$parser = new ParallelParser();
$urls = ['https://ru.wikipedia.org/wiki/Чудеса_света', 'https://ru.wikipedia.org/wiki/Семь_новых_чудес_природы'];

$parser->parsePages($urls);
