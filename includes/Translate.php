<?php

require_once './config.php';
require_once 'functions.php';

function Translate($params, $chunk_items, $has_transliterate = false, $params2 = "", $has_sourceText = true, $has_translateText = true) {

    $requestBody = [];
    foreach ($chunk_items as $chunk_key => $chunk_item) {
        $requestBody[]['Text'] = $chunk_item['value'];
        if (!$has_sourceText) {
            $chunk_items[$chunk_key]['value'] = '';
        }
    }

    $content = json_encode($requestBody);

    $headers = "Content-type: application/json\r\n" .
        "Content-length: " . strlen($content) . "\r\n" .
        "Ocp-Apim-Subscription-Key: ". TRANSLATOR_TEXT_SUBSCRIPTION_KEY . "\r\n" .
        "X-ClientTraceId: " . com_create_guid() . "\r\n";

    $options = array (
        'http' => array (
            'header' => $headers,
            'method' => 'POST',
            'content' => $content
        )
    );

    $context  = stream_context_create ($options);

    if ($has_translateText) {
        $path = "/translate?api-version=3.0";
        $result = @file_get_contents(TRANSLATOR_TEXT_ENDPOINT . $path . $params, false, $context);
        if ($result === false) {
            echo "Có lỗi xảy ra. Vui lòng kiểm tra. ";
            $error = error_get_last();
            echo '<pre>';
            print_r($error);
            echo '</pre>';
            exit;
        }
        $result = json_decode($result, true);
        if ($result) {
            foreach ($result as $key => $item) {
                if (isset($item['translations'][0]['text'])) {
                    if (empty($chunk_items[$key]['value'])) {
                        $chunk_items[$key]['value'] = $item['translations'][0]['text'];
                    } else {
                        $chunk_items[$key]['value'] .= "\r\n" . $item['translations'][0]['text'];
                    }
                }
            }
        }
    }

    //transliterate
    if ($has_transliterate) {
        $path = "/transliterate?api-version=3.0";
        $result = @file_get_contents (TRANSLATOR_TEXT_ENDPOINT . $path . $params2, false, $context);
        if ($result === false) {
            echo "Có lỗi xảy ra. Vui lòng kiểm tra. ";
            $error = error_get_last();
            echo '<pre>';
            print_r($error);
            echo '</pre>';
            exit;
        }

        $result = json_decode($result, true);
        if ($result) {
            foreach ($result as $key => $item) {
                if (isset($item['text'])) {
                    if (empty($chunk_items[$key]['value'])) {
                        $chunk_items[$key]['value'] .= $item['text'];
                    } else {
                        $chunk_items[$key]['value'] .= "\r\n" . $item['text'];
                    }
                }
            }
        }
    }

    return $chunk_items;
}

?>
