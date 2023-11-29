<?php

function find_h2_headers($html) {
    $doc = new DOMDocument();
    $doc->loadHTML($html);

    $headers = $doc->getElementsByTagName("h2");

    foreach ($headers as $header) {
        echo $header->textContent . PHP_EOL;
    }
}

$html = '<html>
<head>
  <title>Заголовок</title>
</head>
<body>
  <h2>header 1</h2>
  <h2>header 2</h2>
  <h2>header 3</h2>
</body>
</html>';

find_h2_headers($html);
