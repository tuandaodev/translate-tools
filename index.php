<?php

require_once 'vendor/autoload.php';

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
    <title>Translate Excel - Translation Tools</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>

<?php

$page="translate";

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

<?php
    require_once './includes/nav.php';
?>

<div class="container">
    <div class="py-5 text-center">
        <h2>Translation Tools</h2>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h4 class="mb-3">Translate Excel Content</h4>
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <label for="country">Language Source</label>

                        <select class="custom-select d-block w-100" id="" name="translation_source" required="">
                            <option value="0">Auto Detected</option>
                            <?php
                            build_list_options($translation_languages, $translation_source_selected);
                            ?>
                        </select>
                        <div class="invalid-feedback">
                            Please select a valid country.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="state">Language Destination</label>
                        <select class="custom-select d-block w-100" id="" name="translation_destination" required="">
                            <?php
                            build_list_options($translation_languages, $translation_destination_selected);
                            ?>
                        </select>
                    </div>
                </div>

                <hr class="mb-4">

                <div class="row">
                    <div class="col-md-6">
                        <label for="country">Tùy chọn</label>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="save-info"
                                   name="has_transliteration">
                            <label class="custom-control-label" for="save-info">Phiên Âm</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="state">Transliteration Language</label>
                        <select class="custom-select d-block w-100" id="" name="transliteration_source">
                            <?php
                            build_list_options_transliteration_languages($transliteration_languages, $transliteration_source_selected);
                            ?>
                        </select>
                    </div>
                </div>

                <hr class="mb-4">
                <div class="row">
                    <div class="col-md-6 mb-6">
                        <label for="cc-expiration">Input File (Excel)</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="importfile" name="importfile"
                                   required="true"/>
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

    <?php
        require_once './includes/footer.php';
    ?>
</div>
</body>
</html>