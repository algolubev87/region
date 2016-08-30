<?php

echo 'Это словарь соответствий<br>';
echo '<pre>';

$currentVocabFileNames = glob('./vocab/*.csv');
if (is_array($currentVocabFileNames) && count($currentVocabFileNames)) {
    $currentVocabFileName = $currentVocabFileNames[0];
    $encodedCurrentVocab = csv_to_array($currentVocabFileName, ';');
}
function csv_to_array($filename = '', $delimiter = ',') {
    if (!file_exists($filename) || !is_readable($filename))
        return FALSE;

    $header = NULL;
    $data = array();
    if (($handle = fopen($filename, 'r')) !== FALSE) {
        while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
            if (!$header) {
                $header = $row;
            } else {
                $data[] = array($header, $row);
            }
        }
        fclose($handle);
    }
    return $data;
}
