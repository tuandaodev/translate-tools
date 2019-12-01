<?php

function build_list_options($list, $selected = -1) {
    foreach ($list as $key => $item) {
        if ($selected == $key) {
            echo "<option value=\"{$key}\" selected>{$item['name']} - {$item['nativeName']}</option>";
        } else {
            echo "<option value=\"{$key}\">{$item['name']} - {$item['nativeName']}</option>";
        }
    }
}

function build_list_options_transliteration_languages($list, $selected = -1) {
    foreach ($list as $key => $items) {
        if (isset($items['scripts']) && !empty($items['scripts'])) {
            foreach ($items['scripts'] as $item) {
                if ($item['code'] == 'Latn') continue;
                if ($selected == $item['code']) {
                    echo "<option value=\"{$item['code']}\" selected>{$item['name']} - {$item['nativeName']}</option>";
                } else {
                    echo "<option value=\"{$item['code']}\">{$item['name']} - {$item['nativeName']}</option>";
                }
            }
        }
    }
}

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