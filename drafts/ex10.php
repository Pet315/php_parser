<?php

function get_first_header($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    $doc = new DOMDocument();
    $doc->loadHTML($result);

    $headers = $doc->getElementsByTagName("h1");
    $header = $headers->item(0);

    return $header->textContent;
}

$pid = pcntl_fork();

if ($pid == 0) {
    $url = "https://ru.wikipedia.org/wiki/Чудеса_света";
    $header = get_first_header($url);
    echo "h1 on page 1: $header\n";

    $pid = pcntl_fork();

    if ($pid == 0) {
        $url = $header;
        $header = get_first_header($url);
        echo "h1 on page 2: $header\n";
    }
} else {
    pcntl_wait($status);
}
