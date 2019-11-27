<?php

require_once 'vendor\autoload.php';
require_once 'includes/Transliterate.php';

// Transliterate text in Japanese from Japanese script (i.e. Hiragana/Katakana/Kanji) to Latin script.
$params = "&language=ja&fromScript=jpan&toScript=latn";

// Transliterate "good afternoon".
$text = "こんにちは";

$requestBody = array(
    array(
        'Text' => $text,
    ),
    array(
        'Text' => 'は 日本語で何ですか',
    ),
    array(
        'Text' => 'さようなら',
    ),
);
$content = json_encode($requestBody);

$result = Transliterate($params, $content);

echo '<pre>';
print_r($result);
echo '</pre>';
exit;

// Note: We convert result, which is JSON, to and from an object so we can pretty-print it.
// We want to avoid escaping any Unicode characters that result contains. See:
// http://php.net/manual/en/function.json-encode.php
$json = json_encode(json_decode($result), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
echo $json;

