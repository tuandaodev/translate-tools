<?php

require_once 'vendor\autoload.php';
require_once 'includes/Translate.php';

$params = "&to=vi";

$text = "Hello, world!";

$requestBody = array(
    array(
        'Text' => $text,
    ),
    array(
        'Text' => 'Good Morning',
    ),
    array(
        'Text' => 'I have a concern on this',
    ),
);
$content = json_encode($requestBody);

$result = Translate($params, $content);

echo '<pre>';
print_r($result);
echo '</pre>';
exit;

// Note: We convert result, which is JSON, to and from an object so we can pretty-print it.
// We want to avoid escaping any Unicode characters that result contains. See:
// http://php.net/manual/en/function.json-encode.php
$json = json_encode(json_decode($result), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
echo $json;

