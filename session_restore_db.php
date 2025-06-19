<?php
session_start();
include_once("connectdb.php");

$session_id = $_SESSION['ses_id'];
$result = $conn->query("SELECT data FROM session_data_fwd WHERE session_id = '$session_id'");

if ($row = $result->fetch_assoc()) {
    $_SESSION['currency_data'] = json_decode($row['data'], true); // แปลง JSON กลับเป็นอาเรย์
    echo '<p style="color: green;">โหลดข้อมูล Session จากฐานข้อมูลสำเร็จ</p>';
} else {
    echo '<p style="color: red;">โหลดข้อมูล Session จากฐานข้อมูลสำเร็จไม่สำเร็จ</p>';
}

$conn->close();
?>