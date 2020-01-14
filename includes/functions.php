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

if (!function_exists('internalFormatConvertTime')) {
    function internalFormatConvertTime($internal_format)
    {
        $file_content = '';

        foreach ($internal_format as $k => $block) {
            $nr = $k + 1;
            $start = $block['start'];
            $end = $block['end'];

            if ($block['lines']) {
                $lines = implode("\r\n", $block['lines']);
            } else {
                $lines = $block['lines'];
            }

            $file_content .= $nr . "\r\n";
            $file_content .= $start . "\r\n";
            $file_content .= $end . "\r\n";
            $file_content .= $lines . "\r\n";
            $file_content .= "\r\n";
        }

        $file_content = trim($file_content);

        return $file_content;
    }
}

function filterFilename($filename, $beautify = true)
{
    // sanitize filename
    $filename = preg_replace(
        '~
        [<>:"/\\|?*]|            # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
        [\x00-\x1F]|             # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
        [\x7F\xA0\xAD]|          # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
        [#\[\]@!$&\'()+,;=]|     # URI reserved https://tools.ietf.org/html/rfc3986#section-2.2
        [{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
        ~x',
        '-', $filename);
    // avoids ".", ".." or ".hiddenFiles"
    $filename = ltrim($filename, '.-');
    // optional beautification
    if ($beautify) $filename = beautifyFilename($filename);
    // maximise filename length to 255 bytes http://serverfault.com/a/9548/44086
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $filename = mb_strcut(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($filename)) . ($ext ? '.' . $ext : '');
    return $filename;
}
/**
 *
 * @param string $filename
 * @return string
 */
function beautifyFilename($filename)
{
    // reduce consecutive characters
    $filename = preg_replace(array(
        // "file   name.zip" becomes "file-name.zip"
        '/ +/',
        // "file___name.zip" becomes "file-name.zip"
        '/_+/',
        // "file---name.zip" becomes "file-name.zip"
        '/-+/'
    ), '-', $filename);
    $filename = preg_replace(array(
        // "file--.--.-.--name.zip" becomes "file.name.zip"
        '/-*\.-*/',
        // "file...name..zip" becomes "file.name.zip"
        '/\.{2,}/'
    ), '.', $filename);
    // lowercase for windows/unix interoperability http://support.microsoft.com/kb/100625
    $filename = mb_strtolower($filename, mb_detect_encoding($filename));
    // ".file-name.-" becomes "file-name"
    $filename = trim($filename, '.-');
    return $filename;
}