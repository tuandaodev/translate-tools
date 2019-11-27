<?php

require_once './config.php';

// Transliterate text in Japanese from Japanese script (i.e. Hiragana/Katakana/Kanji) to Latin script.
//$params = "&language=ja&fromScript=jpan&toScript=latn";

if (!function_exists('com_create_guid')) {
  function com_create_guid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
  }
}

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
