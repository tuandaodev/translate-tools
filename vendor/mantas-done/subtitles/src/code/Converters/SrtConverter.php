<?php namespace Done\Subtitles;

class SrtConverter implements ConverterContract {

    /**
     * Converts file's content (.srt) to library's "internal format" (array)
     *
     * @param string $file_content      Content of file that will be converted
     * @return array                    Internal format
     */
    public function fileContentToInternalFormat($file_content)
    {
        $internal_format = []; // array - where file content will be stored

        $matches = $this->splitData($file_content);
        foreach ($matches as $match) {
            $times = $this->timeMatch($match[0]);
            $text = $this->textMatch(isset($match[1]) ? $match[1] : '');

            $internal_format[] = [
                'start' => static::srtTimeToInternal($times['start_time']),
                'end' => static::srtTimeToInternal($times['end_time']),
                'lines' => $text, // get all the remaining lines from block (if multiple lines of text)
            ];
        }

        return $internal_format;
    }

    private function splitData($data)
    {
        //find digits followed by a single line break and timestamps
        $sections = preg_split('/\d+(?:\r\n|\r|\n)(?=(?:\d\d:\d\d:\d\d,\d\d\d)\s-->\s(?:\d\d:\d\d:\d\d,\d\d\d))/m', $data,-1,PREG_SPLIT_NO_EMPTY);
        $matches = [];
        foreach ($sections as $section) {
            //cleans out control characters, borrowed from https://stackoverflow.com/a/23066553
            $section = preg_replace('/[^\PC\s]/u', '', $section);
            if(trim($section) == '') continue;
            $matches[] = preg_split('/(\r\n|\r|\n)/', $section, 2,PREG_SPLIT_NO_EMPTY);
        }
        return $matches;
    }

    private static function timeMatch($timeString)
    {
        $matches = [];
        preg_match_all('/(\d\d:\d\d:\d\d,\d\d\d)\s-->\s(\d\d:\d\d:\d\d,\d\d\d)/', $timeString, $matches,
            PREG_SET_ORDER);
        $time = $matches[0];
        return [
            'start_time' => $time[1],
            'end_time'   => $time[2]
        ];
    }
    private static function textMatch($textString)
    {
        $text = rtrim($textString);
        if ($text) {
            $text = explode("\r\n", $text);
            if ($text)
                return $text;
            return '';
        }

        return '';
    }

    /**
     * Convert library's "internal format" (array) to file's content
     *
     * @param array $internal_format    Internal format
     * @return string                   Converted file content
     */
    public function internalFormatToFileContent(array $internal_format)
    {
        $file_content = '';

        foreach ($internal_format as $k => $block) {
            $nr = $k + 1;
            $start = static::internalTimeToSrt($block['start']);
            $end = static::internalTimeToSrt($block['end']);

            if ($block['lines']) {
                $lines = implode("\r\n", $block['lines']);
            } else {
                $lines = $block['lines'];
            }

            $file_content .= $nr . "\r\n";
            $file_content .= $start . ' --> ' . $end . "\r\n";
            $file_content .= $lines . "\r\n";
            $file_content .= "\r\n";
        }

        $file_content = trim($file_content);

        return $file_content;
    }

    // ------------------------------ private --------------------------------------------------------------------------

    /**
     * Convert .srt file format to internal time format (float in seconds)
     * Example: 00:02:17,440 -> 137.44
     *
     * @param $srt_time
     *
     * @return float
     */
    protected static function srtTimeToInternal($srt_time)
    {
        $parts = explode(',', $srt_time);

        $only_seconds = strtotime("1970-01-01 {$parts[0]} UTC");
        $milliseconds = (float)('0.' . $parts[1]);

        $time = $only_seconds + $milliseconds;

        return $time;
    }

    /**
     * Convert internal time format (float in seconds) to .srt time format
     * Example: 137.44 -> 00:02:17,440
     *
     * @param float $internal_time
     *
     * @return string
     */
    protected static function internalTimeToSrt($internal_time)
    {
        $parts = explode('.', $internal_time); // 1.23
        $whole = $parts[0]; // 1
        $decimal = isset($parts[1]) ? substr($parts[1], 0, 3) : 0; // 23

        $srt_time = gmdate("H:i:s", floor($whole)) . ',' . str_pad($decimal, 3, '0', STR_PAD_RIGHT);

        return $srt_time;
    }
}
