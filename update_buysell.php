<?php
session_start();
require_once("connectdb.php");
header("Content-Type: application/json");

$response = ["status" => "error", "message" => "Invalid data"];

// รับค่าพารามิเตอร์
$session_id = $_SESSION['ses_id'] ?? '';
$year = $_POST['y'] ?? '';
$month = $_POST['m'] ?? '';
$branchID = $_POST['bid'] ?? '';
$buysell = $_POST['buysell'] ?? '';

// ตรวจสอบความครบถ้วนของข้อมูล
if (!$session_id || !$year || !$month || !$branchID || !$buysell) {
    echo json_encode(["status" => "error", "message" => "Missing required parameters"]);
    exit;
}

// แปลง string JSON เป็น array
$buysell_array = json_decode($buysell, true);
if (!is_array($buysell_array)) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON format for buysell"]);
    exit;
}

try {
    // ดึงข้อมูล buysell เดิมจากฐานข้อมูล
    $sql = "SELECT buysell FROM session_data_fwd WHERE session_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // ถ้าไม่มีข้อมูลมาก่อน กำหนด array ว่าง
    $buysell_data = $row ? json_decode($row['buysell'], true) : [];
    if (!is_array($buysell_data)) $buysell_data = [];

    // จัดรูปแบบข้อมูลในรูปแบบ branch → year → month
    if (!isset($buysell_data[$branchID])) {
        $buysell_data[$branchID] = [];
    }
    if (!isset($buysell_data[$branchID][$year])) {
        $buysell_data[$branchID][$year] = [];
    }

    // เพิ่มหรืออัปเดตเดือน
    $buysell_data[$branchID][$year][$month] = $buysell_array;

    // แปลงกลับเป็น JSON
    $new_buysell_json = json_encode($buysell_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!$new_buysell_json) {
        echo json_encode(["status" => "error", "message" => "Failed to encode JSON"]);
        exit;
    }

    // บันทึกลงฐานข้อมูล (INSERT หรือ UPDATE)
    $insert_sql = "INSERT INTO session_data_fwd (session_id, buysell) 
                   VALUES (?, ?) 
                   ON DUPLICATE KEY UPDATE buysell = ?";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("sss", $session_id, $new_buysell_json, $new_buysell_json);
    
    if ($insert_stmt->execute()) {
        $response = [
            "status" => "success",
            "message" => "Buysell data updated successfully",
            "data" => $buysell_data
        ];
    } else {
        $response = [
            "status" => "error",
            "message" => "Failed to update buysell data"
        ];
    }

    // เก็บ session currency_data ถ้ามี
    if (isset($_SESSION['currency_data'])) {
        $session_data_json = json_encode($_SESSION['currency_data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $data_stmt = $conn->prepare("UPDATE session_data_fwd SET data = ? WHERE session_id = ?");
        $data_stmt->bind_param("ss", $session_data_json, $session_id);
        $data_stmt->execute();
        $data_stmt->close();
    }

    // ปิดการเชื่อมต่อ
    $stmt->close();
    $insert_stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response = ["status" => "error", "message" => $e->getMessage()];
}

// ส่งผลลัพธ์ JSON กลับ
echo json_encode($response);
?>
