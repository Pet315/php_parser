<?php
include 'urls.php';

function request( $url, $postdata = null, $cookiefile = 'tmp/cookie.txt') {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:47.0) Gecko/20100101 Firefox/47.0');

    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_PROXY, '34.77.56.122:8080');
    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);

    curl_setopt($ch, CURLOPT_TIMEOUT, 9);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);

    if ($postdata) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    }

    $html = curl_exec($ch);
    curl_close($ch);
    return $html;
}

$url = 'https://ru.wikipedia.org/wiki/Чудеса_света';
echo request($url);
