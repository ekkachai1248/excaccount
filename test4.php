<?php
session_start();
$currency_data = $_SESSION['currency_data'] ?? [];
$ym = "2023-01";
$currency = "USD";

$data = $currency_data[$ym][$currency] ?? [];

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตาราง USD - มกราคม 2023</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-4 text-primary">ตาราง USD - มกราคม 2023</h2>

        <?php if (!empty($data)): ?>
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>วันที่</th>
                        <th>อัตราแลกเปลี่ยน</th>
                        <th>ยอดเงิน</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $entry): ?>
                        <tr>
                            <td><?= $entry['date']; ?></td>
                            <td><?= number_format($entry['rate'], 2); ?></td>
                            <td><?= number_format($entry['amount'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning">ไม่มีข้อมูลสำหรับ USD ในเดือนมกราคม 2023</div>
        <?php endif; ?>
    </div>
</body>
</html>
