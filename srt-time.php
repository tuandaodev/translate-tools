<?php

require_once 'vendor/autoload.php';
require_once 'includes/functions.php';

use Carbon\Carbon;
use Done\Subtitles\Subtitles;

?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Convert Time Format - SRT Tools</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>

<?php

$page="srt-time";

if (isset($_POST['go_convert_time'])) {

    $upload_dir = __DIR__ . '/import-files/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0700);
    } else {
        try {
            //delete old files
            $files = glob($upload_dir . "*"); // get all file names
            foreach ($files as $file) { // iterate files
                if (is_file($file))
                    unlink($file); // delete file
            }
        } catch (Exception $ex) {

        }
    }

    if (isset($_FILES['importfilesrt']['name']) && !empty($_FILES['importfilesrt']['name'])) {
        $file_srt = file_get_contents($_FILES['importfilesrt']['tmp_name']);
        $subtitles = Subtitles::load($file_srt, 'srt');
        $sub_content = $subtitles->getInternalFormat();

        $formatedTimeContent = internalFormatConvertTime($sub_content);

        $file_info = pathinfo($_FILES['importfilesrt']['name']);
        $newFileName = filterFilename($file_info['filename']);
        $file_output = __DIR__ . '/import-files/' . $newFileName . '_exported.' . 'srt';
        $upload_url = 'import-files/';
        $file_output_url = $upload_url . $newFileName . '_exported.' . 'srt';

        file_put_contents($file_output, $formatedTimeContent);

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
            <h4 class="mb-3">SRT Convert Time Format</h4>
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-6">
                        <label for="cc-expiration">Input File (SRT)</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="importfilesrt" name="importfilesrt" />
                            <label class="custom-file-label" for="importfilesrt">Choose file</label>
                        </div>
                    </div>
                </div>

                <hr class="mb-4">
                <button class="btn btn-primary btn-lg btn-block" type="submit" name="go_convert_time" value="true">Go Convert
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