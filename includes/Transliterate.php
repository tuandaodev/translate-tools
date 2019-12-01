<?php

require_once './config.php';
require_once 'functions.php';

// Transliterate text in Japanese from Japanese script (i.e. Hiragana/Katakana/Kanji) to Latin script.
//$params = "&language=ja&fromScript=jpan&toScript=latn";

function Transliterate ($params, $content) {

    $path = "/transliterate?api-version=3.0";

    $headers = "Content-type: application/json\r\n" .
        "Content-length: " . strlen($content) . "\r\n" .
        "Ocp-Apim-Subscription-Key: " . TRANSLATOR_TEXT_SUBSCRIPTION_KEY . "\r\n" .
        "X-ClientTraceId: " . com_create_guid() . "\r\n";

    $options = array (
        'http' => array (
            'header' => $headers,
            'method' => 'POST',
            'content' => $content
        )
    );
    $context  = stream_context_create ($options);
    $result = file_get_contents (TRANSLATOR_TEXT_ENDPOINT . $path . $params, false, $context);
    $result = json_decode($result, true);
    echo '<pre>';
    print_r($result);
    echo '</pre>';
    exit;
    if ($result)
        return $result;
    return false;
}

?>
