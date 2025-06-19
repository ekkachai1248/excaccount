<?php
include('connectdb.php');

// ตรวจสอบว่ามีข้อมูล 'dataOut' หรือไม่
if (isset($_POST['dataOut']) && !empty($_POST['dataOut'])) {
    // กรองข้อมูลที่รับมาจากผู้ใช้เพื่อความปลอดภัย
    $dataOut = $conn->real_escape_string($_POST['dataOut']);
    $created = date('Y-m-d H:i:s');

    // คำสั่ง UPDATE ทันที ไม่ต้องเช็กก่อน
    $updateSql = "UPDATE filter_data_out SET data_out = ?, created_at = ? WHERE id = 1";
    if ($updateStmt = $conn->prepare($updateSql)) {
        // ผูกค่าพารามิเตอร์
        $updateStmt->bind_param("ss", $dataOut, $created);

        // ดำเนินการคำสั่ง UPDATE
        if ($updateStmt->execute()) {
            if ($updateStmt->affected_rows > 0) {
                echo "อัปเดตข้อมูลสำเร็จ!";
            } else {
                echo "ไม่พบข้อมูลที่จะอัปเดต!";
            }
        } else {
            echo "เกิดข้อผิดพลาดในการอัปเดตข้อมูล: " . $updateStmt->error;
        }

        $updateStmt->close();
    } else {
        echo "เกิดข้อผิดพลาดในการเตรียมคำสั่ง UPDATE!";
    }

    // ปิดการเชื่อมต่อฐานข้อมูล
    $conn->close();
} else {
    echo "ไม่พบข้อมูลที่ต้องการบันทึก!";
}
?>
