<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
</head>

<body>
<?php
function getCurrencies() {
    // อ่านไฟล์ JSON
    $jsonData = file_get_contents('currency.json');
    
    // แปลง JSON เป็นอาร์เรย์
    $currencies = json_decode($jsonData, true)['currency'];
    
    // เรียงลำดับข้อมูลตาม ISO (A-Z)
    usort($currencies, function($a, $b) {
        return strcmp($a['iso'], $b['iso']);
    });

    return $currencies;
}
    
$currencies = getCurrencies();
//print_r( $currencies );
    
// แสดงผลลัพธ์
foreach ($currencies as $currency) {
    echo "ISO: " . $currency['iso'] . "<br>";
    echo "Data1: " . $currency['data1'] . "<br>";
    echo "Data2: " . $currency['data2'] . "<hr>";
}
?>
</body>
</html>