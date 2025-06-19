<?php
session_start();

// เชื่อมต่อกับฐานข้อมูล
include_once("connectdb.php");
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<div class="container mt-3">
<button id="generateReportButton" class="btn btn-primary">ไปที่ buying_report_a เดือน 2023-01</button>

<script>
    document.getElementById('generateReportButton').onclick = function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'buying_report_a.php';
        form.target = '_blank';

        form.innerHTML = `
            <input type="hidden" name="y" value="2023">
            <input type="hidden" name="m" value="01">
            <input type="hidden" name="branchID" value="7">
        `;

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    };
</script>


<?php

// ตรวจสอบว่ามีข้อมูลใน session หรือไม่
if (!isset($_SESSION['currency_data'])) {
    echo '<p style="color: red;">ไม่มีข้อมูลใน Session</p>';
    exit;
}

// ถ้ามีการคลิกลบข้อมูลจาก URL (ลิงก์ "ลบข้อมูล")
if (isset($_GET['ym'])) {
    $ym = $_GET['ym']; // รับค่าปี-เดือนจาก GET

    // ตรวจสอบว่าใน session มีข้อมูลสำหรับเดือนนี้หรือไม่
    if (isset($_SESSION['currency_data'][$ym])) {
        // ลบข้อมูลจาก session
        unset($_SESSION['currency_data'][$ym]);

        // อัปเดตข้อมูลในตาราง session_data_fwd
        //$session_id = session_id(); // session ID ของผู้ใช้ปัจจุบัน
        $session_id = $_SESSION['ses_id'];
        $data = json_encode($_SESSION['currency_data']); // แปลงข้อมูลใน session เป็น JSON

        // เตรียมคำสั่ง SQL
        $stmt = $conn->prepare("UPDATE session_data_fwd SET data = ?, last_update = CURRENT_TIMESTAMP WHERE session_id = ?");
        $stmt->bind_param('ss', $data, $session_id);
        $stmt->execute();

        // ตรวจสอบว่าอัปเดตสำเร็จหรือไม่
        if ($stmt->affected_rows > 0) {
            echo "<p>ลบข้อมูลเดือน $ym เสร็จสิ้น</p>";
        } else {
            echo "<p>ไม่พบข้อมูลในตาราง session_data_fwd ที่จะอัปเดต</p>";
        }

        $stmt->close();
    } else {
        echo "<p>ไม่พบข้อมูลใน session สำหรับเดือน $ym</p>";
    }

    $conn->close();

    // กลับไปที่หน้าหลัก
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// สร้างช่วงปีและเดือนที่ต้องการตรวจสอบ
$startYear = 2023;
$endYear = 2027;

echo "<h3>สถานะข้อมูลใน SESSION (ปี $startYear - $endYear)</h3>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>ปี-เดือน</th><th>สถานะ</th><th>&nbsp;</th></tr>";

for ($year = $startYear; $year <= $endYear; $year++) {
    for ($month = 1; $month <= 12; $month++) {
        $ym = sprintf("%d-%02d", $year, $month); // สร้างค่า "ปี-เดือน" เช่น 2023-01, 2023-02

        // ตรวจสอบว่าใน session มีข้อมูลใน key นี้หรือไม่
        if (isset($_SESSION['currency_data'][$ym]) && !empty($_SESSION['currency_data'][$ym])) {
            echo "<tr><td>$ym</td><td style='color: green;'>✅ มีข้อมูล</td><td><a href='?ym=$ym'>ลบข้อมูล</a></td></tr>";
        } else {
            echo "<tr><td>$ym</td><td style='color: red;'>❌ ไม่มีข้อมูล</td><td>&nbsp;</td></tr>";
        }
    }
}

echo "</table>";
?>
</div>