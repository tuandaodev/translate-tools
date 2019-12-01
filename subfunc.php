<?php

use Done\Subtitles\Subtitles;

require_once 'vendor/autoload.php';
require_once 'includes/Translate.php';
require_once 'includes/Transliterate.php';


$subtitles = Subtitles::load('captions.srt');

$sub_content = $subtitles->getInternalFormat();

//$sub_content = reset($sub_content);
foreach ($sub_content as $key => $sub_row) {

//    foreach ($sub_row['lines'] as $item_text) {
//
//    }
    //$sub_content[$key]['lines'][] = "TEST";

    $sub_content[$key]['lines'] = [];
}

$subtitles->setInternalFormat($sub_content);

$subtitles->save('captions_new.srt');

$subtitles = Subtitles::load('captions_new.srt');

echo "DONE";
echo '<pre>';
print_r($subtitles->getInternalFormat());
echo '</pre>';
exit;

foreach ($subtitles as $key => $sub_row) {
    echo '<pre>';
    print_r($sub_row);
    echo '</pre>';
}

exit;
//$subtitles->content();
echo '<pre>';
print_r($subtitles);
echo '</pre>';
exit;