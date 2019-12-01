<?php

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

require_once 'includes/Translate.php';
require_once 'includes/Transliterate.php';

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

    $endTime = DateTime::createFromFormat('Y-m-d H:i:s,u', "0-01-01" . $_REQUEST['endTime']);
    $startTime = DateTime::createFromFormat('Y-m-d H:i:s,u', "0-01-01" . $_REQUEST['startTime']);

    $has_transliteration = false;
    $params2 = "";
    if (isset($_POST['has_transliteration']) && $_POST['has_transliteration'] == 'on') {
        $has_transliteration = true;
        $params2 = "&language={$translation_source_selected}&fromScript={$transliteration_source_selected}&toScript=latn";
    }

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

    if ($translation_source_selected) {
        $params = "&from={$translation_source_selected}&to={$translation_destination_selected}";
    } else {
        $params = "&to={$translation_destination_selected}";
    }

    $import_chunks = array_chunk($import_rows, 10);

    foreach ($import_chunks as $chunk_key => $chunk_item) {
        $list_temp = Translate($params, $chunk_item, $has_transliteration, $params2);
        if ($list_temp) {
            $import_chunks[$chunk_key] = $list_temp;
        }
        unset($list_temp);
    }

    foreach ($import_chunks as $chunk) {
        foreach ($chunk as $value) {
            if (!empty($value)) {
                $worksheet->getCell($value['pos'], false)->setValue($value['value']);
            }
        }
    }

    $file_info = pathinfo($_FILES['importfile']['name']);
    $file_output = __DIR__ . '/import-files/' . $file_info['filename'] . '_exported.' . $file_info['extension'];
    $upload_url = 'import-files/';
    $file_output_url = $upload_url . $file_info['filename'] . '_exported.' . $file_info['extension'];

    $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

    $writer->save($file_output);

    header("Location: {$file_output_url}");
    exit;
}
?>


<body>

<div class="container">
    <div class="py-5 text-center">
        <h2>SRT Tools</h2>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h4 class="mb-3">Add Text to SRT</h4>
            <form method="POST" enctype="multipart/form-data">

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="firstName">Start Time</label>
                        <input type="text" class="form-control" id="" name="startTime" placeholder="" value="00:01:41,800">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="lastName">End Time</label>
                        <input type="text" class="form-control" id="" name="endTime" placeholder="" value="00:01:58,780">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="firstName">Time per word (second)</label>
                        <input type="text" class="form-control" id="" name="timePerWord" placeholder="" value="0.4">
                    </div>
                </div>
                <hr class="mb-4">
                <div class="row">
                    <div class="col-md-6 mb-6">
                        <label for="cc-expiration">Input File (Excel)</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="importfile" name="importfile" />
                            <label class="custom-file-label" for="importfile">Choose file</label>
                        </div>
                    </div>
                </div>
                <hr class="mb-4">
                <button class="btn btn-primary btn-lg btn-block" type="submit" name="go_translate" value="true">Go
                    Translate
                </button>
            </form>
        </div>
    </div>

    <footer class="my-5 pt-5 text-muted text-center text-small">
        <p class="mb-1"><a href="https://freelancerhcm.com" target="_blank">FreelancerHCM.Com</a></p>
    </footer>
</div>


<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
        crossorigin="anonymous"></script>
<script src="scripts.js"></script>

</body>
</html>