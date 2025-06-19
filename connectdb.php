<?php
$servername = "changtaiexchange.com";
$username = "ctxadmin";
$password = "Zd7!x2n3";
$dbname = "lgh_01_001_ctx";

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//echo "Connected successfully";



// ปิดการเชื่อมต่อ
//$conn->close();
?>
