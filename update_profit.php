<?php
session_start();
require_once("connectdb.php");

header("Content-Type: application/json");

$response = ["status" => "error", "message" => "Invalid data"];

// รับค่า session_id, year, month, profit, branchID
$session_id = $_SESSION['ses_id'] ?? '';
$year = $_POST['y'] ?? '';
$month = $_POST['m'] ?? '';
$profit_value = $_POST['profit'] ?? '';
$branchID = $_POST['bid'] ?? '';  // รหัสสาขาเป็น root key

// ตรวจสอบค่าพารามิเตอร์
if (!$session_id || !$year || !$month || !$branchID) {
    echo json_encode(["status" => "error", "message" => "Missing session ID, branch ID, year, or month"]);
    exit;
}

try {
    // ดึงข้อมูล profit ที่มีอยู่
    $sql = "SELECT profit FROM session_data_fwd WHERE session_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // ถ้าไม่มีข้อมูล, กำหนดเป็น array ว่าง
    $profit_data = $row ? json_decode($row['profit'], true) : [];

    // ถ้ายังไม่มี branchID ให้สร้าง root ใหม่
    if (!isset($profit_data[$branchID])) {
        $profit_data[$branchID] = [];
    }

    // ถ้าไม่มีปีในสาขานั้น ให้สร้างปีใหม่
    if (!isset($profit_data[$branchID][$year])) {
        $profit_data[$branchID][$year] = [];
    }

    // อัปเดตเดือนในสาขา/ปีนั้น
    $profit_data[$branchID][$year][$month] = $profit_value;

    // แปลงข้อมูลใหม่เป็น JSON
    $new_profit_json = json_encode($profit_data, JSON_UNESCAPED_UNICODE);
    if (!$new_profit_json) {
        echo json_encode(["status" => "error", "message" => "JSON encoding error"]);
        exit;
    }

    // บันทึกลงฐานข้อมูล
    $insert_sql = "INSERT INTO session_data_fwd (session_id, profit) 
                   VALUES (?, ?) 
                   ON DUPLICATE KEY UPDATE profit = ?";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("sss", $session_id, $new_profit_json, $new_profit_json);
    
    if ($insert_stmt->execute()) {
        $response = ["status" => "success", "message" => "Updated/Inserted successfully", "data" => $profit_data];
    } else {
        $response = ["status" => "error", "message" => "Failed to update/insert data"];
    }

    // ✅ ส่วนเก็บ session data เพิ่มเติม
    if (isset($_SESSION['currency_data'])) {
        $session_data = json_encode($_SESSION['currency_data']); // แปลงเป็น JSON
        $stmt = $conn->prepare("UPDATE session_data_fwd SET data = ? WHERE session_id = ? ");
        $stmt->bind_param("ss", $session_data, $session_id);
        $stmt->execute();
    }

    // ปิดการเชื่อมต่อ
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response = ["status" => "error", "message" => $e->getMessage()];
}

// ส่งผลลัพธ์ในรูปแบบ JSON
echo json_encode($response);
?>
