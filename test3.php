<?php
session_start();

// ตรวจสอบว่ามีข้อมูลใน Session หรือไม่
$currency_data = $_SESSION['currency_data'] ?? [];

//print_r($currency_data);
//echo "<hr>";

// ค่าที่รับมาจากฟอร์ม
$filter_ym = $_GET['ym'] ?? '';
$filter_currency = strtoupper(trim($_GET['currency'] ?? ''));

// ฟังก์ชันกรองข้อมูล
$filtered_data = [];
if ($filter_ym || $filter_currency) {
    foreach ($currency_data as $year_month => $currencies) {
        if ($filter_ym && $year_month !== $filter_ym) continue;

        foreach ($currencies as $currency => $entries) {
            if ($filter_currency && stripos($currency, $filter_currency) === false) continue;

            $filtered_data[$year_month][$currency] = $entries;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตารางอัตราแลกเปลี่ยน</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-4">ค้นหาข้อมูลอัตราแลกเปลี่ยน</h2>

        <!-- ฟอร์มค้นหา -->
        <form method="GET" class="mb-4">
            <div class="form-row">
                <div class="col-md-3">
                    <label>เลือกเดือน-ปี</label>
                    <select name="ym" class="form-control">
                        <option value="">-- ทั้งหมด --</option>
                        <?php
                        $months = [
                            '2023-01', '2023-02', '2023-03', '2023-04', 
                            '2023-05', '2023-06', '2023-07', '2023-08', 
                            '2023-09', '2023-10', '2023-11', '2023-12'
                        ];
                        foreach ($months as $month) {
                            $selected = ($filter_ym === $month) ? 'selected' : '';
                            echo "<option value='$month' $selected>$month</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>กรอกสกุลเงิน</label>
                    <input type="text" name="currency" class="form-control" placeholder="เช่น USD, JPY, GBP" value="<?= htmlspecialchars($filter_currency); ?>">
                </div>
                <div class="col-md-3 align-self-end">
                    <button type="submit" class="btn btn-primary">ค้นหา</button>
                    <a href="?" class="btn btn-secondary">ล้างค่า</a>
                </div>
            </div>
        </form>

        <!-- แสดงข้อมูลที่ค้นหา -->
        <?php if ($filter_ym || $filter_currency): ?>
            <h3 class="text-danger">ผลการค้นหา</h3>
            <?php if (!empty($filtered_data)): ?>
                <?php foreach ($filtered_data as $year_month => $currencies): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h3 class="mb-0"><?= htmlspecialchars($year_month); ?></h3>
                        </div>
                        <div class="card-body">
                            <?php foreach ($currencies as $currency => $entries): ?>
                                <h4 class="text-success"><?= htmlspecialchars($currency); ?></h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>วันที่</th>
                                                <th>อัตราแลกเปลี่ยน</th>
                                                <th>ยอดเงิน</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($entries as $entry): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($entry['date']); ?></td>
                                                    <td><?= $entry['rate'] !== null ? number_format($entry['rate'], 2) : '-'; ?></td>
                                                    <td><?= $entry['amount'] !== null ? number_format($entry['amount'], 2) : '-'; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning">ไม่พบข้อมูลที่ค้นหา</div>
            <?php endif; ?>
        <?php endif; ?>

<hr><hr>
        
        <!-- แสดงข้อมูลทั้งหมด -->
        <h3 class="mt-5">ข้อมูลทั้งหมด</h3>
        <?php if (!empty($currency_data)): ?>
            <?php foreach ($currency_data as $year_month => $currencies): ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0"><?= htmlspecialchars($year_month); ?></h3>
                    </div>
                    <div class="card-body">
                        <?php foreach ($currencies as $currency => $entries): ?>
                            <h4 class="text-success"><?= htmlspecialchars($currency); ?></h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>วันที่</th>
                                            <th>อัตราแลกเปลี่ยน</th>
                                            <th>ยอดเงิน</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($entries as $entry): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($entry['date']); ?></td>
                                                <td><?= $entry['rate'] !== null ? number_format($entry['rate'], 2) : '-'; ?></td>
                                                <td><?= $entry['amount'] !== null ? number_format($entry['amount'], 2) : '-'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-warning">ไม่มีข้อมูลในระบบ</div>
        <?php endif; ?>
    </div>
</body>
</html>
