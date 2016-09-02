<form method="POST" name="111" enctype="multipart/form-data">
    <label for="file1input">файл с данными от УК
        <input id="file1input" type="file" name="file1" placeholder="файл с данными от УК"></label><hr>
    <label for="file2input">файл с данными СПЕЦДЕПА
        <input id="file2input" type="file" name="file2" placeholder="файл с данными СПЕЦДЕПА"></label><hr>

    <button id="submitInput" type="submit">Запустить проверку</button>
    <br>
    <a href="/manageVocab.php" target="_blank">Словарь соответствий</a>
    <br>
</form>
<style>
    .commonDiv div{
        display: inline-block;
        width: 50%;
        float: left;
    }
    .div1{
        background-color: thistle;
    }
    .div2{

        background-color: bisque;
    }
    .different{
        background-color: #ffbfbf;
    }
    .identical{
        background-color: #c6fdc6;
    }
    .impossible{
        background-color: orange;
    }
    .ISINheader td{
        text-align: left;
        padding-top: 5px;
        padding-left: 50px;
        font-weight: bold;
    }
    th,td{
        border: 1px solid black;
    }
    table{
        border-collapse:  collapse;
    }
    table > td {
        width: 30%;
    }
</style>

<?php
//header('Content-Type: text/html; charset=utf-8');
echo '<pre>';


$filename1 = '';
//$filename1 = 'uploads/file1.xtdd';

if (isset($_FILES['file1']['tmp_name'])) {
    $filename1 = $_FILES['file1']['tmp_name'];
}
$filename2 = '';
//$filename1 = 'uploads/file1.xtdd';

if (isset($_FILES['file2']['tmp_name'])) {
    $filename2 = $_FILES['file2']['tmp_name'];
}

if (!$filename1 == '') {
//    die(var_dump($filename1));
    if (file_exists($filename1)) {

        $fileContent1 = file_get_contents($filename1);
    } else {
        die('Файл 1 выбран, но не загрузился на сервер. Проверьте права или что-то ещё на серваке');
    }
} else {
    die('Не выбран файл 1');
}
if (!$filename2 == '') {
//    die(var_dump($filename2));
    if (file_exists($filename2)) {

        $fileContent2 = file_get_contents($filename2);
    } else {
        die('Файл 2 выбран, но не загрузился на сервер. Проверьте права или что-то ещё на серваке');
    }
} else {
    die('Не выбран файл 2');
}

function getExternalVocab() {
    $result = [];
    $currentVocabFileNames = glob('./vocab/*.csv');
    if (is_array($currentVocabFileNames) && count($currentVocabFileNames)) {
        $currentVocabFileName = $currentVocabFileNames[0];
        $result = csv_to_array($currentVocabFileName, ';');
    }

    return $result;
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
                $data[] = $row;
            }
        }
        fclose($handle);
    }
//  var_dump($row);
//    die(var_dump($data));
    return $data;
}

//die(var_dump($encodedCurrentVocab));

$fileContent1 = preg_replace('/<av:ОКУД0420502[^>]+>+/', '<av:ОКУД0420502>', $fileContent1);
$fileContent1 = preg_replace('/<([\\/])av:ОКУД0420502[^>]?>+/', '</av:ОКУД0420502>', $fileContent1);
//print_r($fileContent1);
//die();
$fileContent1 = preg_replace('\'<av:\'', '<', $fileContent1);
$fileContent1 = preg_replace('\'</av:\'', '</', $fileContent1);
$xml1 = simplexml_load_string($fileContent1);
$json1 = json_encode($xml1);
$array1 = json_decode($json1, TRUE);
$filename2 = 'uploads/file2.xtdd';
$fileContent2 = preg_replace('/<av:ОКУД0420502[^>]+>+/', '<av:ОКУД0420502>', $fileContent2);
$fileContent2 = preg_replace('/<([\\/])av:ОКУД0420502[^>]?>+/', '</av:ОКУД0420502>', $fileContent2);
$fileContent2 = preg_replace('\'<av:\'', '<', $fileContent2);
$fileContent2 = preg_replace('\'</av:\'', '</', $fileContent2);
$xml2 = simplexml_load_string($fileContent2);
$json2 = json_encode($xml2);
$array2 = json_decode($json2, TRUE);
$resultArray1 = [];
$resultArray2 = [];

//die(var_dump($fileContent1));
echo '<pre>';
//die();
groupArraysByRules($array1, $array2);
groupArraysByRules($array2, $array1);
groupArraysByRules($array1, $array2, null, true);

//var_dump($array1);
$resultArray = $array1;
if (is_array($resultArray) && count($resultArray)) {
    echo '<table>
    <th>сравниваемый параметр</th>
    <th>значение в файле №1</th>
    <th>значение в файле №2</th>';
    printResult($resultArray);
    echo '</table>';
}
echo '<pre>';

//var_dump($array1);
//var_dump($array2);

function printResult($resultArray) {

    foreach ($resultArray as $keyRes => $valueRes) {
//        var_dump($valueRes['header']);
        if (!isset($valueRes['isNotHeader'])) {

//            echo '<tr><td colspan="3">' . $keyRes . '</td></tr>';
        }
        if (is_array($valueRes)) {
            if (count($valueRes)) {
                if (!isset($valueRes['diff'])) {
                    if (isset($valueRes['isISIN'])) {
                        if ($keyRes == 'File') {

                            echo '<tr class="ISINheader"><td colspan="3">' . $keyRes . '</td><tr>';
                        } else {
                            echo '<tr class="ISINheader"><td colspan="3">' . $keyRes . '</td><tr>';
                        }
                    }
                    printResult($valueRes);
                } else {



                    if (isset($valueRes['diff'])) {
                        if ($keyRes != 'isISIN') {
                            if ($keyRes == 'File') {
                                echo '<tr class="' . $valueRes['diff'] . '"><td>' . $keyRes . '</td><td>' . (strlen($valueRes['value1']) > 50 ? 'есть прикрепленный файл' : 'нет файла') . '</td><td>' . (strlen($valueRes['value2']) > 50 ? 'есть прикрепленный файл' : 'нет файла') . '</td><tr>';
//                                die(var_dump(strlen($valueRes['value1'])));
                            } else {
//                                var_dump(mb_detect_encoding($valueRes['value1']));
//                                var_dump($valueRes['value1']);
//                                echo '<hr>';
                                echo '<tr class="' . $valueRes['diff'] . '"><td>' . $keyRes . '</td><td>' . $valueRes['value1'] . '</td><td>' . $valueRes['value2'] . '</td><tr>';
                            }
                        }
                    }
//                    }
                }
            } else {
                if ($keyRes != 'File') {
                    echo '<tr><td>' . $keyRes . '</td><td>нет значения</td><td>нет значения</td><tr>';
                }
            }
        }
    }
}

function createSkeletonCopy(&$resource) {
//    die(var_dump($resource));
    $resource = 'нет значения';
}

function groupArraysByRules(&$array1, $array2, $keyName1 = '', $executeCompare = false) {
//    if ($executeCompare === TRUE) {
////        var_dump($executeCompare);
//    }

    foreach ($array1 as $key1 => &$value1) {
//        if ($executeCompare === TRUE) {
////            var_dump($key1);
//        }
        if (is_array($value1)) {
            if (count($value1)) {
//                if ($executeCompare === TRUE) {
                if (isset($array2[$key1])) {
                    $value2 = $array2[$key1];
                } else {
                    $value2 = $value1;
                    array_walk_recursive($value2, 'createSkeletonCopy');
//                            var_dump($value2);
//                    foreach ($value1 as $keyNF => $valueNF) {
//                        if (is_array($valueNF) && count($valueNF)) {
//
//
////                            foreach ($valueNF as $keyNF2 => $valueNF2) {
////
////                                $value2[$keyNF][$keyNF2] = 'VVVVV';
////                            }
//                        } else {
//                            $value2[$keyNF] = 'XXXXX';
//                        }
//                    }
//                        $value1['notFoundInarray2'] = $key1;
//                    }
                }
                if ($key1 == 'РасшифровкиРаздела3') {
                    if (is_array($value1) && count($value1)) {
                        if ($executeCompare !== TRUE) {
                            $value1 = section3decoding($value1);
                        }
                    }
                }
                if ($key1 == 'Подраздел8ДебиторскаяЗадолж') {
//                    die(var_dump($value1));
                    if (is_array($value1) && count($value1)) {
                        if ($executeCompare !== TRUE) {
                            $value1 = section8decoding($value1);
                        }
                    }
                }
                groupArraysByRules($value1, $value2, $key1, $executeCompare);
            } else {

                $value1[$key1] = 'пустой';
            }
        } else {
            if ($executeCompare === TRUE) {
                if (isset($array2[$key1])) {
                    $value2 = $array2[$key1];
                } else {
                    $value2 = $value1;
                    if (is_array($value2)) {
                        array_walk_recursive($value2, 'createSkeletonCopy');
                    }
                }
                $value1 = compareString($value1, $value2, $key1);
            }
        }
    }
}

function section3decoding($array) {
    $result = [];
    if (is_array($array) && count($array)) {
        foreach ($array as $key1 => $value1) {
//            die(var_dump($key1));

            if ($key1 == 'Подраздел2_ЦенБумРосЭмитент' || $key1 == 'Подраздел3ЦенБумИнострЭмит' || $key1 == 'Подраздел3ЦенБумИнострЭмит'
            ) {
                if (is_array($value1) && count($value1)) {
                    foreach ($value1 as $keyLEV2 => $valueLEV2) {
                        if (is_array($valueLEV2) && count($valueLEV2)) {
                            foreach ($valueLEV2 as $keyLEV3 => $valueLEV3) {
                                $counter = 0;
                                foreach ($valueLEV3 as $keyLEV4 => $valueLEV4) {
                                    if (is_array($valueLEV3) && count($valueLEV3)) {
                                        $checkKeysForISIN = FALSE;
                                        if (is_array($valueLEV4) && count($valueLEV4)) {
                                            foreach (array_keys($valueLEV4) as $innerKey => $ISIN) {
                                                if (strpos($ISIN, 'КодISIN') !== FALSE) {
                                                    $checkKeysForISIN = $ISIN;
                                                    $isinCode = $valueLEV4[$checkKeysForISIN];
                                                    $isinCodeOriginal = $valueLEV4[$checkKeysForISIN];
                                                    if (isset($value1[$keyLEV2][$keyLEV3][$valueLEV4[$checkKeysForISIN]])) {
                                                        $counter++;
                                                        $isinCode = $isinCode . '-----' . $counter;
//                                                                var_dump($valueLEV5);
//                                                    echo $counter;
//                                                                echo $checkKeysForISIN;
//                                                    echo '<br>';
                                                    } else {
                                                        $counter = 0;
                                                    }
                                                    $value1[$keyLEV2][$keyLEV3][$isinCode] = $array[$key1][$keyLEV2][$keyLEV3][$keyLEV4];
                                                    $value1[$keyLEV2][$keyLEV3][$valueLEV4[$checkKeysForISIN]]['isISIN'] = true;
                                                    if ($counter > 0) {
                                                        $value1[$keyLEV2][$keyLEV3][$isinCode]['это дубль'] = 'да';
                                                        $value1[$keyLEV2][$keyLEV3][$isinCode]['Очень похож на'] = $isinCodeOriginal;
                                                    }
//                                                echo $keyLEV3;
                                                    $value1[$keyLEV2][$keyLEV3]['containsISIN'][$isinCode] = $valueLEV4[$checkKeysForISIN];
                                                    if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4])) {
                                                        unset($value1[$keyLEV2][$keyLEV3][$keyLEV4]);
                                                    }
                                                }
//                                            var_dump($value1[$keyLEV2][$keyLEV3]);
//                                                asort($value1[$keyLEV2][$keyLEV3]);
                                            }
                                        }
                                        if ($checkKeysForISIN === FALSE && is_array($valueLEV4) && count($valueLEV4)) {
                                            $counter = 0;
                                            foreach ($valueLEV4 as $keyLEV5 => $valueLEV5) {
                                                if (is_array($valueLEV4) && count($valueLEV4)) {
                                                    $checkKeysForISIN = FALSE;
                                                    if (is_array($valueLEV5) && count($valueLEV5)) {
                                                        foreach (array_keys($valueLEV5) as $innerKey => $ISIN) {
                                                            if (strpos($ISIN, 'КодISIN') !== FALSE) {
                                                                $checkKeysForISIN = $ISIN;
                                                                $isinCode = $valueLEV5[$checkKeysForISIN];
                                                                $isinCodeOriginal = $valueLEV5[$checkKeysForISIN];
                                                                if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$valueLEV5[$checkKeysForISIN]])) {
                                                                    $counter++;
                                                                    $isinCode = $isinCode . '-----' . $counter;
//                                                                var_dump($valueLEV5);
//                                                                echo $counter;
//                                                                echo $checkKeysForISIN;
//                                                                echo '<br>';
                                                                } else {
                                                                    $counter = 0;
                                                                }
//                                                            $checkKeysForISIN=$checkKeysForISIN.'-----'.$counter;
//                                                            }
//                                                                var_dump($valueLEV5[$checkKeysForISIN]);
                                                                $value1[$keyLEV2][$keyLEV3][$keyLEV4][$isinCode] = $array[$key1][$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5];
//                                                            die(var_dump($value1[$keyLEV2][$keyLEV3][$keyLEV4]));
                                                                $value1[$keyLEV2][$keyLEV3][$keyLEV4][$valueLEV5[$checkKeysForISIN]]['isISIN'] = true;
                                                                if ($counter > 0) {
                                                                    $value1[$keyLEV2][$keyLEV3][$keyLEV4][$isinCode]['это дубль'] = 'да';
                                                                    $value1[$keyLEV2][$keyLEV3][$keyLEV4][$isinCode]['Очень похож на'] = $isinCodeOriginal;
                                                                }
                                                                $value1[$keyLEV2][$keyLEV3][$keyLEV4]['containsISIN'][$isinCode] = $valueLEV5[$checkKeysForISIN];

                                                                if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5])) {
                                                                    unset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5]);
                                                                }
                                                            }
                                                        }
//                                                            asort($value1[$keyLEV2][$keyLEV3][$keyLEV4]);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {

            }

            $result[$key1] = $value1;
//            print_r($result);
        }
    }
    return $result;
}

function section8decoding($array) {
    $result = [];


//    return $result;
    if (is_array($array) && count($array)) {
        foreach ($array as $key1 => $value1) {
            die(var_dump($key1));
            if (is_array($value1) && count($value1)) {
                foreach ($value1 as $keyLEV2 => $valueLEV2) {
//                    asort($valueLEV2);
                    if (is_array($valueLEV2) && count($valueLEV2)) {
                        foreach ($valueLEV2 as $keyLEV3 => $valueLEV3) {
                            $counter = 0;
                            foreach ($valueLEV3 as $keyLEV4 => $valueLEV4) {
//                    var_dump($valueLEV4);
                                if (is_array($valueLEV3) && count($valueLEV3)) {
                                    $checkForMoneySumm = FALSE;
                                    if (is_array($valueLEV4) && count($valueLEV4)) {
                                        foreach (array_keys($valueLEV4) as $innerKey => $moneySumm) {
                                            if (strpos($moneySumm, 'СуммаДенСредств') !== FALSE) {
                                                $checkForMoneySumm = $moneySumm;
                                                $moneyValue = $valueLEV4[$checkForMoneySumm];
                                                $moneyValueOriginal = $valueLEV4[$checkForMoneySumm];
                                                if (isset($value1[$keyLEV2][$keyLEV3][$valueLEV4[$checkForMoneySumm]])) {
                                                    $counter++;
                                                    $moneyValue = $moneyValue . '-----' . $counter;
//                                                                var_dump($valueLEV5);
//                                                    echo $counter;
//                                                                echo $checkKeysForISIN;
//                                                    echo '<br>';
                                                } else {
                                                    $counter = 0;
                                                }
                                                $value1[$keyLEV2][$keyLEV3][$moneyValue] = $array[$key1][$keyLEV2][$keyLEV3][$keyLEV4];
                                                $value1[$keyLEV2][$keyLEV3][$valueLEV4[$checkForMoneySumm]]['isISIN'] = true;
                                                if ($counter > 0) {
                                                    $value1[$keyLEV2][$keyLEV3][$moneyValue]['это дубль'] = 'да';
                                                    $value1[$keyLEV2][$keyLEV3][$moneyValue]['Очень похож на'] = $moneyValueOriginal;
                                                }
//                                                echo $keyLEV3;
                                                $value1[$keyLEV2][$keyLEV3]['containsISIN'][$moneyValue] = $valueLEV4[$checkForMoneySumm];
                                                if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4])) {
                                                    unset($value1[$keyLEV2][$keyLEV3][$keyLEV4]);
                                                }
                                            }
//                                            var_dump($value1[$keyLEV2][$keyLEV3]);
//                                                asort($value1[$keyLEV2][$keyLEV3]);
                                        }
                                    }
                                    if ($checkForMoneySumm === FALSE && is_array($valueLEV4) && count($valueLEV4)) {
                                        $counter = 0;
                                        foreach ($valueLEV4 as $keyLEV5 => $valueLEV5) {
                                            if (is_array($valueLEV4) && count($valueLEV4)) {
                                                $checkForMoneySumm = FALSE;
                                                if (is_array($valueLEV5) && count($valueLEV5)) {
                                                    foreach (array_keys($valueLEV5) as $innerKey => $moneySumm) {
                                                        if (strpos($moneySumm, 'КодISIN') !== FALSE) {
                                                            $checkForMoneySumm = $moneySumm;
                                                            $moneyValue = $valueLEV5[$checkForMoneySumm];
                                                            $moneyValueOriginal = $valueLEV5[$checkForMoneySumm];
                                                            if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$valueLEV5[$checkForMoneySumm]])) {
                                                                $counter++;
                                                                $moneyValue = $moneyValue . '-----' . $counter;
//                                                                var_dump($valueLEV5);
//                                                                echo $counter;
//                                                                echo $checkKeysForISIN;
//                                                                echo '<br>';
                                                            } else {
                                                                $counter = 0;
                                                            }
//                                                            $checkKeysForISIN=$checkKeysForISIN.'-----'.$counter;
//                                                            }
//                                                                var_dump($valueLEV5[$checkKeysForISIN]);
                                                            $value1[$keyLEV2][$keyLEV3][$keyLEV4][$moneyValue] = $array[$key1][$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5];
//                                                            die(var_dump($value1[$keyLEV2][$keyLEV3][$keyLEV4]));
                                                            $value1[$keyLEV2][$keyLEV3][$keyLEV4][$valueLEV5[$checkForMoneySumm]]['isISIN'] = true;
                                                            if ($counter > 0) {
                                                                $value1[$keyLEV2][$keyLEV3][$keyLEV4][$moneyValue]['это дубль'] = 'да';
                                                                $value1[$keyLEV2][$keyLEV3][$keyLEV4][$moneyValue]['очень похож на'] = $moneyValueOriginal;
                                                            }
                                                            $value1[$keyLEV2][$keyLEV3][$keyLEV4]['containsISIN'][$moneyValue] = $valueLEV5[$checkForMoneySumm];

                                                            if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5])) {
                                                                unset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5]);
                                                            }
                                                        }
                                                    }
//                                                            asort($value1[$keyLEV2][$keyLEV3][$keyLEV4]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $result[$key1] = $value1;
//            print_r($result);
        }
    }
    return $result;
}

function srtringsIdentical($str1, $str2) {
    $result = false;
//    var_dump($str1);
//    var_dump($str2);
//    var_dump($result);

    $vocabularySRC = array(
        'Решение о выпуске',
        '',
        'обыкновенная',
        '00.00.0000',
        '00.00.0000',
        'не установлен',
        '119049, г. Москва, ул.Шаболовка, д.10, корпус 2',
        'по купонному доходу по ценным бумагам',
        '119034,ГОРОД МОСКВА,,,,ПЕРЕУЛОК ГАГАРИНСКИЙ,3',
        'Привилегированные акции',
        'Обязательство по выплате дивидендов'
    );
    $vocabularyDEST = array(
        'Условия выпуска ценных бумаг',
        '',
        'Обыкновенные акции',
        'не установлена',
        'не установлен',
        '00,00,0000',
        '119049, г. МосКва, ул,Шаболовка, д.10,корп.2',
        'Накопленный купонный доход',
        '119034, г. Москва, Гагаринский пер., д.3.',
        'привилегированная'
        ,
        'Дивиденды начисленные'
    );


    $encodedCurrentVocab = getExternalVocab();
    foreach ($encodedCurrentVocab as $value) {
//        var_dump($value);
//        die(print_r(mb_convert_encoding($value[0],"utf-8")));
//        die(print_r(mb_convert_encoding($value[0],'UTF-8',  mb_detect_encoding($value[0]))));
        $vocabularySRC[] = $value[0];
        $vocabularyDEST[] = $value[1];
    }
//die(var_dump($vocabularyDEST));
    $str1 = trim($str1);
    $str2 = trim($str2);
    $pattern = array('/,/', '/\\s{1,}/', '/«/', '/»/');
    $replacement = array('.', '', '"', '"');


    foreach ($vocabularySRC as $phrase) {
        if (trim($phrase) != '') {
            $phrase = preg_replace('/\\s{1,}/', '', $phrase);
            $pattern[] = '/' . str_replace(',', '.', trim($phrase)) . '/i';
        } else {
            $pattern[] = '/еслиДажеЭтоДобавильВОтчетностьТоЯТогдаНеЗнаюЧтоДелать/';
        }
    }
    foreach ($vocabularyDEST as $phrase) {
        if (trim($phrase) != '') {
            $phrase = preg_replace('/\\s{1,}/', '', $phrase);
            $replacement[] = str_replace(',', '.', trim($phrase));
        } else {
            $replacement[] = 'ТУТ УЖАСНАЯ ОШИБКА!!!!!!!!!!!!!!!!!';
        }
    }

//    var_dump($pattern);
//    die(var_dump($replacement));
//    var_dump($replacement);
//die(var_dump($pattern));
    $str1 = preg_replace($pattern, $replacement, $str1);
    $str2 = preg_replace($pattern, $replacement, $str2);
    $str1 = mb_strtolower($str1);
    $str2 = mb_strtolower($str2);


    if ($str1 == $str2) {
        $result = true;
    }

//    var_dump($str1);
//    var_dump($str2);
//    var_dump($result);
//    echo '<hr>';
    return $result;
}

function compareString($string1, $string2, $key1) {
    $result = array();

//    var_dump($string1,$string2,$key1);

    $result['value1'] = $string1;
    $result['value2'] = $string2;
//    $result['isNotHeader'] = true;

    $result['diff'] = 'different';
    $parsingError = FALSE;
    if (is_array($string1)) {
        if (count($string1)) {
            $parsingError = 'В файле 1 есть строка "' . $key1 . '" у которой есть вложенные элементы';
        } else {
            $parsingError = 'В файле 1 есть пустая строка "' . $key1 . '" которая распарсилась как массив';
        }
        $result['value1'] = $parsingError;
    }
    if (is_array($string2)) {
        if (count($string2)) {
            $parsingError = 'В файле 2 есть строка "' . $key1 . '" у которой есть вложенные элементы';
        } else {
            $parsingError = 'В файле 2 есть пустая строка "' . $key1 . '" которая распарсилась как массив';
        }
        $result['value2'] = $parsingError;
    }
    $result['parsingErrors'] = $parsingError;
    if ($result['parsingErrors'] === FALSE) {


        $isIdentical = srtringsIdentical($string1, $string2);
        if ($isIdentical === true) {
            $result['diff'] = 'identical';
        }


//
//$vocabularySRC = array(
//    'Условия выпуска ценных бумаг'
//        )
//
//;
//$vocabularyDEST = array(
//    'Условия выпуска ценных бумаг'
//        )
//
//;
//
//        $string1 = preg_replace('/\s{2,}/', ' ', $string1);
//        $string2 = preg_replace('/\s{2,}/', ' ', $string2);
//        $string1 = preg_replace('/00.00.0000/', 'не установлена', $string1);
//
//
//
//
//
//        if ($string1) == ($string2)) {
//            $result['diff'] = 'identical';
//        }else {
//
//            $stringToFloat1 = str_replace(',', '.', $string1);
//            $stringToFloat2 = str_replace(',', '.', $string2);
//            if ($stringToFloat1 == $stringToFloat2) {
//                $result['diff'] = 'identical';
//            } else {
//                $quotes = ['«', '»'];
//                $stringQut1 = str_replace($quotes, '"', $string1);
//                $stringQut2 = str_replace($quotes, '"', $string2);
//
//                if ($stringQut1 == $stringQut2) {
//                    $result['diff'] = 'identical';
//                }
//            }
//        }
//        else {
//
//            $stringToFloat1 = str_replace(',', '.', $string1);
//            $stringToFloat2 = str_replace(',', '.', $string2);
//            if ($stringToFloat1 == $stringToFloat2) {
//                $result['diff'] = 'identical';
//            } else {
//                $quotes = ['«', '»'];
//                $stringQut1 = str_replace($quotes, '"', $string1);
//                $stringQut2 = str_replace($quotes, '"', $string2);
//
//                if ($stringQut1 == $stringQut2) {
//                    $result['diff'] = 'identical';
//                }
//            }
//        }
    } else {
        $result['diff'] = 'impossible';
    }
//    echo '<hr>';
//    var_dump($string1, $string2, $key1, $result['diff'], $result['parsingErrors']);
//    echo '<hr>';
//    echo '<hr>';
    return $result;
}

die();




echo '<pre>';
//var_dump($resultArray1);
//    var_dump($value1);
echo '</pre>';






echo '<div>';
echo '<div style="    width: 50%;
    display: inline-block;
    position: relative;
    top: 0;
    vertical-align: top;">';
//echo '<pre>';
//
//print_r($array1);
//echo '</pre>';
echo '</div>';
echo '<div style="    width: 50%;
    display: inline-block;
    position: relative;
    top: 0;
    vertical-align: top;">';
//echo '<pre>';
//print_r($array2);
//echo '</pre>';
echo '</div>';
echo '</div>';
$result1 = [];
$result2 = [];

