<?php

require_once 'vendor/autoload.php';

use Carbon\Carbon;
use Done\Subtitles\Subtitles;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

require_once 'includes/Translate.php';

?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Add Text to Srt - SRT Tools</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>

<?php

$page="srt";
$url = TRANSLATOR_TEXT_ENDPOINT . "/languages?api-version=3.0";

$json = file_get_contents($url);
$api_options_array = json_decode($json, true);

$translation_languages = [];
$transliteration_languages = [];
if ($api_options_array) {
    $translation_languages = $api_options_array['translation'];
    $transliteration_languages = $api_options_array['transliteration'];
}

$translation_source_selected = isset($_REQUEST['translation_source']) ? $_REQUEST['translation_source'] : 'zh-Hans';
$translation_destination_selected = isset($_REQUEST['translation_destination']) ? $_REQUEST['translation_destination'] : 'vi';

$transliteration_source_selected = isset($_REQUEST['transliteration_source']) ? $_REQUEST['transliteration_source'] : 'Hans';

if (isset($_POST['go_translate'])) {

    $count_by_mb_str = false;
    if (isset($_POST['count_by_mb_str']) && $_POST['count_by_mb_str'] == 'on') {
        $count_by_mb_str = true;
    }

    $timePerWord = isset($_REQUEST['timePerWord']) ? $_REQUEST['timePerWord'] : 0;

    $upload_dir = __DIR__ . '/import-files/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0700);
    }

    $import_file_name = $_FILES['importfile']['name'];
    $file_input = $_FILES['importfile']['tmp_name'];

    $reader = new Xlsx();

    $spreadsheet = $reader->load($file_input);
    $worksheet = $spreadsheet->getActiveSheet();

    $import_rows = array();
    foreach ($worksheet->getRowIterator() AS $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(FALSE); // This loops through all cells,
        $cells = [];
        foreach ($cellIterator as $cell) {
            $value = $cell->getValue();

            $col = $cell->getColumn();

            if (!$value || $col != "A") continue;

            $temp['value'] = $value;
            $row = $cell->getRow();
            $temp['pos'] = $col . $row;

            $import_rows[] = $temp;
            unset($temp);
        }
    }

    $totalWords = 0;

    foreach ($import_rows as $key => $item) {
        $item['value'] = trim($item['value']);

        $item['value'] = str_replace("\r\n", "\n", $item['value']);
        $lines = explode("\n", $item['value']);
        if (is_array($lines)) {
            $import_rows[$key]['value'] = $lines;
        } else {
            $import_rows[$key]['value'][] = $item['value'];
        }

        if ($count_by_mb_str) {
            $count = mb_strlen($import_rows[$key]['value'][0], 'utf8');
        } else {
            $count = str_word_count($import_rows[$key]['value'][0]);
        }

        $import_rows[$key]['count'] = $count;
        $totalWords += $count;
    }


    if (isset($_FILES['importfilesrt']['name']) && !empty($_FILES['importfilesrt']['name'])) {
        $file_srt = file_get_contents($_FILES['importfilesrt']['tmp_name']);
        $OLDsubtitles = Subtitles::load($file_srt, 'srt');
        $sub_content = $OLDsubtitles->getInternalFormat();

        $subtitles = new Subtitles();

        $endTime = 0;
        foreach ($sub_content as $key => $sub_row) {
            $first_element = [];
            if (count($import_rows) > 0) {
                $first_element = array_shift($import_rows);
            } else {
                break;
            }

            $endTime = $sub_row['end'];
            $subtitles->add($sub_row['start'], $sub_row['end'], $first_element['value']);
        }

        //$subtitles->setInternalFormat($sub_content);

        $currentTime = $endTime + 0.5;
        $endCurrentTime = $currentTime;
        if (count($import_rows) > 0) {
            foreach ($import_rows as $key => $import_row) {
                $endCurrentTime = $currentTime + $import_row['count'] * $timePerWord;
                $subtitles->add($currentTime, $endCurrentTime , $import_row['value'], false);
                $currentTime = $endCurrentTime;
            }
        }

        //$sub_content = $subtitles->getInternalFormat();


        $file_info = pathinfo($_FILES['importfilesrt']['name']);
        $file_output = __DIR__ . '/import-files/' . $file_info['filename'] . '_exported.' . 'srt';
        $upload_url = 'import-files/';
        $file_output_url = $upload_url . $file_info['filename'] . '_exported.' . 'srt';

        $subtitles->save($file_output);

        header("Location: {$file_output_url}");
        exit;

    } else {

        $baseTime = Carbon::createFromFormat('Y-m-d H:i:s,u', "0000-01-01 00:00:00,00");
        $startTemp = Carbon::createFromFormat('Y-m-d H:i:s,u', "0000-01-01 " . $_REQUEST['startTime']);
        $endTemp = Carbon::createFromFormat('Y-m-d H:i:s,u', "0000-01-01 " . $_REQUEST['endTime']);

        $startTime = $startTemp->diffInMilliseconds($baseTime);
        $startTime = $startTime / 1000;
        $endTime = $endTemp->diffInMilliseconds($baseTime);
        $endTime = $endTime / 1000;

        $currentTime = $startTime;
        $subtitles = new Subtitles();


        //Tinh thoi gian giua cac cau
        $tongSoCau = count($import_rows);
        //echo "Tong so cau: " . $tongSoCau;
        //echo '<br/>';
        $totalTime = $endTime - $startTime;
        //echo "Total Time: {$totalTime}" . '<br/>';
        $totalWordsTime = $totalWords * $timePerWord;
        //echo "totalWordsTime: {$totalTime}" . '<br/>';
//        echo '<pre>';
//        print_r($import_rows);
//        echo '</pre>';
//        exit;

        /**
         * Check đếm chữ tượng hình
         */
        $check_chu_tuong_hinh = false;
        $count_0 = 0;
        foreach ($import_rows as $item) {
            if ($item['count'] == 0) $count_0++;
        }
        if ($count_0 == count($import_rows)) {
            echo "Đang không đếm được từ trong câu. Vui lòng chọn Tùy chọn chữ tượng hình.";
            exit;
        }
        /**
         * End check chữ tượng hình
         */

        if ($totalTime - $totalWordsTime < 0) {
            echo "Tổng thời gian không đủ để thực hiện sub. Vui lòng thử lại.<br/>";
            echo "Start Time - End Time = {$totalTime} giây.<br/>";
            echo "[Tổng số từ] x [Thời gian mỗi từ] = {$totalWords} x {$timePerWord} = " . gmdate("H:i:s", $totalWordsTime) . " giây<br/>";
            exit;
        }
        $timeBetweenSentense = ($totalTime - $totalWordsTime)*1000;

        //echo $tongSoCau;
        //exit;

        $timeBetweenSentense = (float)$timeBetweenSentense/($tongSoCau - 1);
        $timeBetweenSentense = (float)$timeBetweenSentense/1000;
        $timeBetweenSentense = round($timeBetweenSentense, 2);

        //echo $timeBetweenSentense;
        //exit;
        //exit;

        foreach ($import_rows as $key => $item) {
            $endCurrentTime = $currentTime + ($item['count'] * $timePerWord);
            $subtitles->add($currentTime, $endCurrentTime, $item['value'], false);
            $currentTime = $endCurrentTime + $timeBetweenSentense;
        }

        $file_info = pathinfo($_FILES['importfile']['name']);
        $file_output = __DIR__ . '/import-files/' . $file_info['filename'] . '_exported.' . 'srt';
        $upload_url = 'import-files/';
        $file_output_url = $upload_url . $file_info['filename'] . '_exported.' . 'srt';

        $subtitles->save($file_output);

        header("Location: {$file_output_url}");
        exit;
    }
}
?>


<body>

<?php
require_once './includes/nav.php';
?>

<div class="container">
    <div class="py-5 text-center">
        <h2>SRT Tools</h2>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h4 class="mb-3">Add Text to SRT</h4>
            <form method="POST" enctype="multipart/form-data">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="firstName">Start Time</label>
                        <input type="text" class="form-control" id="" name="startTime" placeholder="" value="00:02:00,000">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="lastName">End Time</label>
                        <input type="text" class="form-control" id="" name="endTime" placeholder="" value="00:10:30,000">
                    </div>
                </div>
                <hr class="mb-4">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="firstName">Time per word (second)</label>
                        <input type="text" class="form-control" id="" name="timePerWord" placeholder="" value="0.4" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="country">Tùy chọn</label>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="save-info" name="count_by_mb_str" checked>
                            <label class="custom-control-label" for="save-info">Chữ tượng hình (Trung Quốc, Hàn Quốc, Nhật...)</label>
                        </div>
                    </div>
                </div>
                <hr class="mb-4">
                <div class="row">
                    <div class="col-md-6 mb-6">
                        <label for="cc-expiration">Input File (Excel)</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="importfile" name="importfile" required />
                            <label class="custom-file-label" for="importfile">Choose file</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-6">
                        <label for="cc-expiration">Input File (SRT)</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="importfilesrt" name="importfilesrt" />
                            <label class="custom-file-label" for="importfilesrt">Choose file</label>
                        </div>
                    </div>
                </div>

                <hr class="mb-4">
                <button class="btn btn-primary btn-lg btn-block" type="submit" name="go_translate" value="true">Go Generate
                </button>
            </form>
        </div>
    </div>

    <?php
    require_once './includes/footer.php';
    ?>

</div>

</body>
</html>