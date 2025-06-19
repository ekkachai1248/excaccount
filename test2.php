<?php
session_start();
if (!isset($_SESSION['currency_data'])) {
    echo "ไม่มีข้อมูลใน Session";
    exit;
}

$data = $_SESSION['currency_data'];
//print_r($data);
//echo "<hr>";
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายการข้อมูลสกุลเงิน</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>

<h2>รายการข้อมูลสกุลเงิน</h2>

<?php foreach ($data as $currency => $records): ?>
    <h3>สกุลเงิน: <?= htmlspecialchars($currency) ?></h3>
    <table>
        <thead>
            <tr>
                <th>วันที่</th>
                <th>สกุลเงิน</th>
                <th>อัตราแลกเปลี่ยน</th>
                <th>จำนวน</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['date']) ?></td>
                    <td><?= htmlspecialchars($row['rate']) ?></td>
                    <td><?= is_numeric($row['amount']) ? number_format((float)$row['amount'], 2) : 'N/A' ?></td>
                    <td><?= is_numeric($row['total']) ? number_format((float)$row['total'], 2) : 'N/A' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endforeach; ?>

</body>
</html>
