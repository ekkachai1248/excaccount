<?php
session_start();

// รับค่าที่ส่งมา
$ym = strval($_POST['ym']) ?? ''; // รับค่า ym
$bid = strval($_POST['bid']) ?? 'default'; // รับค่า bid
$tableData = isset($_POST['tableData']) ? json_decode($_POST['tableData'], true) : [];

// ตรวจสอบว่ามีข้อมูล
if (!empty($ym) && !empty($tableData) && !empty($bid)) {
    // บันทึกข้อมูลลงใน Session โดยแยกตาม bid -> ym
    $_SESSION['currency_data'][$bid][$ym] = $tableData;
    
    echo json_encode(["status" => "success", "message" => "ข้อมูลถูกบันทึกใน session", "bid" => $bid, "ym" => $ym]);
} else {
    echo json_encode(["status" => "error", "message" => "ข้อมูลไม่ถูกต้อง"]);
}
?>


<?php
/*
session_start();

// รับค่าที่ส่งมา
$ym = strval($_POST['ym']) ?? ''; // รับค่า ym
$tableData = isset($_POST['tableData']) ? json_decode($_POST['tableData'], true) : [];

// ตรวจสอบว่ามีข้อมูล
if (!empty($ym) && !empty($tableData)) {
    // บันทึกข้อมูลลงใน Session โดยแยกตาม ym
    $_SESSION['currency_data'][$ym] = $tableData;
    
    echo json_encode(["status" => "success", "message" => "ข้อมูลถูกบันทึกใน session", "ym" => $ym]);
} else {
    echo json_encode(["status" => "error", "message" => "ข้อมูลไม่ถูกต้อง"]);
}
*/
?>

