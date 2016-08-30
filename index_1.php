<form method="POST" name="111" enctype="file">
    <input type="text" value="dfsdfhjkh">
    <input type="file">
    <input type="submit">
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
        background-color: lightcoral;
    }
    .identical{
        background-color: lightgreen;
    }
    th,td{
        border: 1px solid black;
    }
    table{
        border-collapse:  collapse;
    }
</style>

<?php
//echo '<pre>';
//echo '<pre>';
//die(var_dump($_POST));
$filename1 = 'uploads/file1.xtdd';
$fileContent1 = file_get_contents($filename1);
$fileContent1 = preg_replace('\'<av:\'', '<', $fileContent1);
$fileContent1 = preg_replace('\'</av:\'', '</', $fileContent1);
$xml1 = simplexml_load_string($fileContent1);
$json1 = json_encode($xml1);
$array1 = json_decode($json1, TRUE);
$filename2 = 'uploads/file2.xtdd';
$fileContent2 = file_get_contents($filename2);
$fileContent2 = preg_replace('\'<av:\'', '<', $fileContent2);
$fileContent2 = preg_replace('\'</av:\'', '</', $fileContent2);
$xml2 = simplexml_load_string($fileContent2);
$json2 = json_encode($xml2);
$array2 = json_decode($json2, TRUE);
echo '<pre>';
$result1 = [];
$result2 = [];
//var_dump($array1);
//die();
$result = [];
$checkedResult = [];
//setParents($array1, 0, $result);
//setParents($array2, 0, $result);
checkArraysForIdenticalOld($array1, $array2);
//die(print_r($array1));
function checkForIdentical ($array1,$array2,$parentID){
    foreach ($array1 as $key1 => $value1) {
        if (is_array($value1)) {
            if (count($value1) > 1 && array_key_exists('parent', $value1)) {
//                var_dump($value1['parent']);
//                $result1[$key1] = $value1;
                checkArraysForIdentical($value1, $array2[$key1],$value1['parent']);
            }
        } else {
//                var_dump($value1);
            $value2 = $array2[$key1];

            if ($value1 == $value2) {
                $value1 = array('key1' => $key1, 'value1' => $value1, 'key2' => $key1, 'value2' => $value2, 'identical' => 1);
            } else {
                $value1 = array('key1' => $key1, 'value1' => $value1, 'key2' => $key1, 'value2' => $value2, 'different' => 1);
            }
        }
//        }
    }
    return $value1;
    
    
    
    
}

function arrayEqual($a1, $a2)
{
    if (count(array_diff($a1, $a2)))
    	return false;

    foreach ($a1 as $k => $v)
    {
    	if (is_array($v) && !arrayEqual($a1[$k], $a2[$k]))
    		return false;
    }

    return true;
}
//var_dump($array1);
//foreach ($array1 as $key1 => $value1) {
//    if (isset($value1['parent'])) {
//        if ($value1['parent'] == 0) {
//            foreach ($array2 as $key2 => $value2) {
//                if ($key1 == $key2) {
//                    if (is_array($value2) && isset($value1['parent']) && count($value2) > 1) {
//                        
//                        foreach ($value2 as $innerKey2 => $innerValue2) {
//                            
//                            var_dump($value1[$innerKey2]);
//                            var_dump($innerValue2);
//                        }
//                        
//                        
//                    } else {
//                    
//                        echo 'пустой блок '.$key1 ;
//                        
//                    }
//                }
//            }
//        }
//    }
//}
die();
die(print_r($checkedResult));


checkArraysForIdentical($checkedResult, $array2);

function setParents(&$array1, $parent, $result) {
    foreach ($array1 as $inputKey => &$inputValue) {
        if (is_array($inputValue)) {
            if (count($inputValue)) {
//                var_dump($parent);
                $inputValue['parent'] = $parent;
                $result[$inputKey] = $inputValue;
                setParents($inputValue, $inputKey, $result);
            } else {
                $inputValue['parent'] = $parent;
                $result[$inputKey] = $inputValue;
            }
        }
//        var_dump($inputValue);
    }
    return $result;
}

function checkArraysForIdentical($array1, $array2) {
    
}

function checkArraysForIdenticalOld(&$array1, $array2) {
    foreach ($array1 as $key1 => &$value1) {
        if (is_array($value1)) {
//            if (count($value1) > 1 && array_key_exists('parent', $value1)) {
            if (count($value1)) {
                $result1[$key1] = $value1;
//                var_dump($value1);
                checkArraysForIdentical($value1, $array2[$key1]);
            }
        } else {
            $value2 = $array2[$key1];
//            echo $key1.'======'.$value1.'<br>';
//            echo $key1.'======'.$value2.'<br>';
            var_dump($value1);
            if ($value1 == $value2) {
                
                $value1 = array('key1' => $key1, 'value1' => $value1, 'key2' => $key1, 'value2' => $value2, 'identical' => 1);
//                die();
//                $array1['identical']=1;
//                $array2['identical'] = 1;
//                var_dump($value1);
//                echo '<span style="background-color: lightgreen;">' . $value1 . '</span>' . ' совпадает с ' . '<span style="background-color: lightgreen;">' . $value2 . '</span><br>';
            } else {
                $value1 = array('key1' => $key1, 'value1' => $value1, 'key2' => $key1, 'value2' => $value2, 'different' => 1);
//                $array1['different']=1;
//                $array2['different']=1;
//                
//                echo '<span style="background-color: lightcoral;">' . $value1 . '</span>' . ' отличается от ' . '<span style="background-color: lightcoral;">' . $value2 . '</span><br>';
            }
//                var_dump($value1);
        }
//        }
    }
//    return $checkedResult;
}

//var_dump($result1);

die();
echo "</pre>";
echo "<table>";
echo '<tr><th>ключ</th><th>значение из файла 1</th><th>значение из файла 2</th><tr>';
printResult($array1);
echo "</table>";

function printResult($resultArray) {

    foreach ($resultArray as $keyRes => $valueRes) {
        if (is_array($valueRes)) {
//            var_dump("is_array($valueRes)");
            if (count($valueRes)) {
                if (!isset($valueRes['identical']) && !isset($valueRes['different'])) {
                    printResult($valueRes);
//                die(var_dump($valueRes));
                } else {
//                    die(var_dump($valueRes));
                    if (isset($valueRes['identical']) && $valueRes['identical'] == 1) {
                        echo '<tr class="identical"><td>' . $valueRes['key1'] . '</td><td>' . $valueRes['value1'] . '</td><td>' . $valueRes['value2'] . '</td><tr>';
                    }
                    if (isset($valueRes['different']) && $valueRes['different'] == 1) {
//die(var_dump($valueRes));
                        if (is_array($valueRes['key1'])) {
                            var_dump($valueRes);
                        }
//                        echo $valueRes['key1'];
//                        echo '<br>';
//                        echo $valueRes['value1'];
//                        echo '<br>';
//                        echo $valueRes['value2'];
//                        echo '<br>';
//                        echo '<br>';
//                        var_dump( $valueRes['key1']);
//                        echo '<br>';
//                        var_dump( $valueRes['value1']);
//                        echo '<br>';
//                        var_dump( $valueRes['value2']);
//                        echo '<br>';
//                        echo '<br>';
                        echo '<tr class="different"><td>' . $valueRes['key1'] . '</td><td>' . $valueRes['value1'] . '</td><td>' . $valueRes['value2'] . '</td><tr>';
                    }
                }
            } else {
                var_dump($keyRes);
                echo '<tr><td>' . $keyRes . '</td><td>нет значения</td><td>нет значения</td><tr>';
            }
        }
    }
}

//    die(var_dump($resultArray));
//var_dump($array1['Раздел1Реквизиты']);
//var_dump($array2['Раздел1Реквизиты']);
//var_dump($array1);
//die(var_dump($array2));



    