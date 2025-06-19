<?php
session_start();
include_once("connectdb.php");

// รับค่าจาก POST
$session_id = $_SESSION['ses_id'];
$year = $_POST['y'] ?? '';
$iso = strtoupper($_POST['iso'] ?? '');  // แปลงสกุลเงินให้เป็นตัวใหญ่
$revenue = floatval($_POST['revenue'] ?? 0);
$sales = floatval($_POST['sales'] ?? 0);
$branchID = $_POST['bid'] ?? '';  // รับรหัสสาขา

// ตรวจสอบ input
//if (!$year || !$iso || !$branchID || $revenue < 0 || $sales < 0) {
    //exit("❌ Error: Missing or invalid data.");
//}

// ดึงข้อมูล revenue ปัจจุบันจากฐานข้อมูล
$sql = "SELECT revenue FROM session_data_fwd WHERE session_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// ถ้ามีข้อมูลอยู่แล้วให้ใช้ มิฉะนั้นสร้างโครง JSON ใหม่
$revenueData = $row ? json_decode($row['revenue'], true) : [];

// ถ้ายังไม่มี branch นี้ ให้สร้างโครงสร้างใหม่
if (!isset($revenueData[$branchID])) {
    $revenueData[$branchID] = [];
}

// ถ้ายังไม่มีปีนี้ในสาขานี้ ให้สร้างปีใหม่
if (!isset($revenueData[$branchID][$year])) {
    $revenueData[$branchID][$year] = [];
}

// อัปเดตรายการที่มีอยู่
$revenueUpdated = false;
foreach ($revenueData[$branchID][$year] as &$entry) {
    if ($entry['iso'] === $iso) {
        $entry['revenue'] = $revenue; 
        $entry['sales'] = $sales;     
        $entry['updated_at'] = date("Y-m-d H:i:s");
        $revenueUpdated = true;
        break;
    }
}

// ถ้าไม่มีรายการนี้ ให้เพิ่มใหม่
if (!$revenueUpdated) {
    $revenueData[$branchID][$year][] = [
        "iso" => $iso,
        "revenue" => $revenue,
        "sales" => $sales,
        "updated_at" => date("Y-m-d H:i:s")
    ];
}

// แปลงกลับเป็น JSON
$revenueJson = json_encode($revenueData, JSON_UNESCAPED_UNICODE);

// ใช้ Transaction ป้องกันข้อมูลเสียหาย
$conn->begin_transaction();

try {
    // บันทึกข้อมูลลงในฐานข้อมูล
    $sql = "INSERT INTO session_data_fwd (session_id, revenue) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE revenue = VALUES(revenue)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $session_id, $revenueJson);

    if ($stmt->execute()) {
        $conn->commit();  // ยืนยันการบันทึก
        echo "✅ Revenue and sales updated successfully.";
    } else {
        throw new Exception("❌ Error updating revenue and sales.");
    }
} catch (Exception $e) {
    $conn->rollback(); // ยกเลิกการเปลี่ยนแปลงถ้ามีปัญหา
    echo $e->getMessage();
}

// ปิดการเชื่อมต่อ
$stmt->close();
$conn->close();
?>
