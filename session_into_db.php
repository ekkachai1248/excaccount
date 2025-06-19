<?php
session_start();
include_once("connectdb.php");

$session_id = $_SESSION['ses_id'];

if (isset($_SESSION['currency_data']) && !empty($_SESSION['currency_data'])) {
    $session_data = json_encode($_SESSION['currency_data'], JSON_UNESCAPED_UNICODE); // เก็บเป็น JSON พร้อมรองรับภาษาไทย

    // ตรวจสอบว่ามี session_id นี้ใน DB แล้วหรือยัง
    $stmt = $conn->prepare("SELECT session_id FROM session_data_fwd WHERE session_id = ?");
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // อัปเดตข้อมูล
        $stmt = $conn->prepare("UPDATE session_data_fwd SET data = ? WHERE session_id = ?");
        $stmt->bind_param("ss", $session_data, $session_id);
    } else {
        // แทรกข้อมูลใหม่
        $stmt = $conn->prepare("INSERT INTO session_data_fwd (session_id, data) VALUES (?, ?)");
        $stmt->bind_param("ss", $session_id, $session_data);
    }

    // ดำเนินการบันทึก
    if ($stmt->execute()) {
        echo '<p style="color: green;">บันทึก Session ลงฐานข้อมูลสำเร็จ</p>';
    } else {
        echo '<p style="color: red;">บันทึก Session ลงฐานข้อมูลไม่สำเร็จ: ' . $stmt->error . '</p>';
    }
} else {
    echo '<p style="color: orange;">ไม่มีข้อมูล Session ที่ต้องการบันทึก</p>';
}

$conn->close();
?>


<?php
/*
session_start();
include_once("connectdb.php");

$session_id = $_SESSION['ses_id'];

    if (isset($_SESSION['currency_data'])) {
        $session_data = json_encode($_SESSION['currency_data']); // แปลงเป็น JSON

        $stmt = $conn->prepare("UPDATE session_data_fwd SET data = ? WHERE session_id = ? ");
        $stmt->bind_param("ss",$session_data, $session_id);
            if ($stmt->execute()) {
                echo '<p style="color: green;">บันทึก Session ลงฐานข้อมูลสำเร็จ</p>';
            } else {
                echo '<p style="color: red;">บันทึก Session ลงฐานข้อมูลไม่สำเร็จ</p>';
            }
    }

$conn->close();
*/
?>