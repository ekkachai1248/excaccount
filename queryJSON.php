<?php
function searchCountryByKey($key) {
    // อ่านไฟล์ country.json
    $json = file_get_contents('country.json');
    
    // แปลง JSON เป็น Array
    $data = json_decode($json, true);
    
    // ตรวจสอบว่ามีข้อมูลใน key หรือไม่
    foreach ($data['country'] as $country) {
        if ($country['key'] === $key) {
            return $country;
        }
    }
    
    // หากไม่พบข้อมูลให้คืนค่า null
    return null;
}


function searchCurrencyByKey($key) {
    // อ่านไฟล์ country.json
    $json2 = file_get_contents('currency.json');
    
    // แปลง JSON เป็น Array
    $cc = json_decode($json2, true);

    // ตรวจสอบว่า JSON ถูกแปลงเป็นอาร์เรย์สำเร็จหรือไม่
    if ($cc === null) {
        echo "ไม่สามารถแปลงไฟล์ JSON ได้";
        exit;
    }
    
    // ตรวจสอบว่ามีข้อมูลใน key หรือไม่
    foreach ($cc['currency'] as $currency) {
        if ($currency['iso'] === $key) {
            return $currency;
        }
    }
    
    // หากไม่พบข้อมูลให้คืนค่า null
    return null;
}


// ฟังก์ชันตรวจสอบว่ามีข้อมูลตาม id นี้หรือไม่ เป็น "MC ในประเทศ" "หรือไม่?
function checkIdMCExists($idToCheck) {
    // อ่านข้อมูลจากไฟล์ JSON
    $jsonData5 = file_get_contents('mc_domestic.json');
    $data5 = json_decode($jsonData5, true);

    // ตรวจสอบว่ามี key 'mc_domestic' หรือไม่
    if (isset($data5['mc_domestic'])) {
        // วนลูปตรวจสอบแต่ละรายการ
        foreach ($data5['mc_domestic'] as $item) {
            if ($item['id'] === $idToCheck) {
                return true; // พบ id ที่ต้องการ
            }
        }
    }

    return false; // ไม่พบ id ที่ต้องการ
}                                       

// หาสกุลเงิน เช่น AUD, JPY, USD เป็นต้น
function getCurrencies() {
    $jsonData = file_get_contents('currency.json');
    
    // แปลง JSON เป็นอาร์เรย์
    $currencies = json_decode($jsonData, true)['currency'];
    
    // เรียงลำดับข้อมูลตาม ISO (A-Z)
    usort($currencies, function($a, $b) {
        return strcmp($a['iso'], $b['iso']);
    });

    return $currencies;
}

// แปลงเดือนตัวเลขเป็นเดือนไทย
function convertMonthToThai($monthNumber) {
    $thaiMonths = [
        "01" => "มกราคม", "02" => "กุมภาพันธ์", "03" => "มีนาคม", "04" => "เมษายน",
        "05" => "พฤษภาคม", "06" => "มิถุนายน", "07" => "กรกฎาคม", "08" => "สิงหาคม",
        "09" => "กันยายน", "10" => "ตุลาคม", "11" => "พฤศจิกายน", "12" => "ธันวาคม"
    ];

    // คืนค่าชื่อเดือน หรือ 'ไม่ทราบเดือน' หากหมายเลขไม่ถูกต้อง
    return $thaiMonths[$monthNumber] ;
}
?>
