<?php
include('connectdb.php');

// ดึงข้อมูลจากตาราง (เช่น 'filter_data_out')
$sql2 = "SELECT * FROM filter_data_out ORDER BY created_at DESC LIMIT 1"; // ดึงข้อมูลล่าสุดจากตาราง
$result2 = $conn->query($sql2);

if ($result2->num_rows > 0) {
    // ดึงข้อมูลแถวแรก (หรือข้อมูลที่ต้องการ)
    $row2 = $result2->fetch_assoc();
    $dataOut = $row2['data_out'];
    $dataId = $row2['id'];
} else {
    $dataOut = "";
    $dataId = null;
}


// SQL query to fetch transaction data
/*
$sql = "
SELECT
    ct.id,
    ts.iso,
    tl.branchID,
    b.branchName,
    ct.created,
    ct.customerID,
    ct.tran_status,
    ct.receipt_id,
    c.`name`,
    c.personalID,
    c.country,
    c.login,
    c.cusType,
    ctd.tranID,
    ts.note,
    ts.amountOUT,
    ts.amountIN,
    ts.rateAsset,
    ts.rate 
FROM
    tb2_counter_transaction AS ct
    INNER JOIN tb2_customer AS c ON ct.customerID = c.id
    INNER JOIN tb2_counter_transaction_detail AS ctd ON ct.id = ctd.cTranID
    INNER JOIN tb2_transaction_stock AS ts ON ctd.tranID = ts.tranID
    INNER JOIN tb2_transaction_log AS tl ON ctd.tranID = tl.id
    INNER JOIN tb2_branch AS b ON tl.branchID = b.branchID 
WHERE
    ts.note <> 1
    AND tl.`status` = 1
    AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL', 'INR') 
    AND YEAR(ct.created) >= 2024
GROUP BY
    ct.id 
ORDER BY
    ct.id DESC, tl.branchID ASC, ts.iso ASC, ct.created ASC
LIMIT 100000
";
*/
$sql = "
SELECT
    * 
FROM
    view_2024up_transactions 
ORDER BY
    id DESC, branchID ASC, iso ASC, created ASC
LIMIT 100000
";

// Execute query and fetch transaction data
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกรายการคัดออก</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
    
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Chonburi&family=Kanit&family=Pattaya&family=Prompt&family=Srisakdi&display=swap" rel="stylesheet">
<link rel="icon" href="favicon.ico" type="image/x-icon">

<style>
    table {
        font-family: "Prompt", sans-serif;
        font-style: normal;
        font-size: 14px;
    }
    div {
        font-family: "Prompt", sans-serif;
        font-style: normal;
    }
	        @media print {
            @page {
                size: landscape; /* ตั้งค่ากระดาษเป็นแนวนอน */
                margin: 1cm; /* ตั้งค่าระยะขอบตามต้องการ */
            }
            
        }
    
.dataTables_wrapper .top {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.dataTables_wrapper .bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* จัดช่องค้นหาชิดขวา */
.dataTables_filter {
    margin-left: auto;
}

    
</style>
    
</head>
<body>

<div class="container mt-4">
    <h3>ข้อมูล (ID) รายการที่คัดออก</h3>
    
    <div>
    <textarea rows="7" cols="60"><?php echo htmlspecialchars($dataOut); ?></textarea>
        <button type="submit" class="btn btn-primary" style="width: 150px" id="saveButton">บันทึก</button>
    </div>

    <!-- Filter Buttons -->
    <div class="mb-3 mt-3">
        <h5>กรองข้อมูลตามปี</h5>
        <button class="btn btn-success btn-sm mb-2 filterYear" data-year="all">ดูทุกปี</button>
        <?php
        // ดึงปีปัจจุบัน
        $currentYear = date("Y");

        // วนลูปแสดงปุ่มปีจาก 2023 ถึงปีปัจจุบัน
        for ($year = 2024; $year <= $currentYear; $year++) {
        ?>
            <button class="btn btn-secondary btn-sm mb-2 filterYear" data-year="<?php echo $year; ?>"><?php echo $year; ?></button>
        <?php } ?>
    </div>

    <div class="mb-3">
        <h5>กรองข้อมูลตามสาขา</h5>
        <button class="btn btn-success btn-sm mb-2 filterBranch" data-branch="all">ดูทุกสาขา</button>
        <?php
        $branchResult = $conn->query("SELECT DISTINCT branchName FROM tb2_branch WHERE branchID NOT IN ('5', '6') ");
        while ($branch = $branchResult->fetch_assoc()):
        ?>
            <button class="btn btn-secondary btn-sm mb-2 filterBranch" data-branch="<?php echo $branch['branchName']; ?>"><?php echo $branch['branchName']; ?></button>
        <?php endwhile; ?>
    </div>

    <div class="mb-3">
        <h5>กรองข้อมูลตามสกุลเงิน</h5>
        <button class="btn btn-success btn-sm mb-2 filterISO" data-iso="all">ดูทุกสกุลเงิน</button>
        <?php
        $isoResult = $conn->query("SELECT DISTINCT iso FROM tb2_transaction_stock");
        while ($iso = $isoResult->fetch_assoc()):
        ?>
            <button class="btn btn-secondary btn-sm mb-2 filterISO" data-iso="<?php echo $iso['iso']; ?>"><?php echo $iso['iso']; ?></button>
        <?php endwhile; ?>
    </div>

    <div class="card p-3">
    <table id="transactionTable" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Select</th>
                <th>ID</th>
                <th>Branch</th>
                <th>Customer Name</th>
                <th>ISO</th>
                <th>Amount OUT</th>
                <th>Amount IN</th>
                <th>Note</th>
                <th>Rate</th>
                <th width="150">Transaction Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><button class="btn btn-warning btn-sm" id="selectID">เลือก</button></td>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['branchName']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['iso']; ?></td>
                    <td><?php echo $row['amountOUT']; ?></td>
                    <td><?php echo $row['amountIN']; ?></td>
                    <td><?php echo $row['note']; ?></td>
                    <td><?php echo $row['rate']; ?></td>
                    <td align="center"><?php echo $row['created']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#transactionTable').DataTable({
            "pageLength": 100, // ตั้งค่าแสดง 100 แถว
            //"dom": '<"top"lfp>rt<"bottom"ip><"clear">',
            "dom": '<"top"lpf><"clear">rt<"bottom"ip>',
            "language": {
                "paginate": {"next": "ถัดไป", "previous": "ก่อนหน้า"},
                "search": "ค้นหา:"
            }
        });
        
    let currentText = $('textarea').val().trim();
    let selectedIDs = currentText.split(',').map(id => id.trim());

    $('#transactionTable tbody tr').each(function() {
        let rowID = $(this).find('td:nth-child(2)').text().trim();
        let button = $(this).find('#selectID');

        if (selectedIDs.includes(rowID)) {
            button.removeClass('btn-warning').addClass('btn-info').text('ลบ');
        }
    });

        // Filter by Year
        $('.filterYear').click(function() {
            var year = $(this).data('year');
            if (year == 'all') {
                table.column(9).search('').draw(); // รีเซ็ต filter ปี
            } else {
                table.column(9).search(year).draw(); // Filter by year in the 'created' column
            }
            // เปลี่ยนปุ่มที่เลือกเป็นสีเขียว
            $('.filterYear').removeClass('btn-success').addClass('btn-secondary');
            $(this).removeClass('btn-secondary').addClass('btn-success');
        });

        // Filter by Branch
        $('.filterBranch').click(function() {
            var branch = $(this).data('branch');
            if (branch == 'all') {
                table.column(2).search('').draw(); // รีเซ็ต filter สาขา
            } else {
                table.column(2).search(branch).draw(); // Filter by branch in the 'branchName' column
            }
            // เปลี่ยนปุ่มที่เลือกเป็นสีเขียว
            $('.filterBranch').removeClass('btn-success').addClass('btn-secondary');
            $(this).removeClass('btn-secondary').addClass('btn-success');
        });

        // Filter by ISO
        $('.filterISO').click(function() {
            var iso = $(this).data('iso');
            if (iso == 'all') {
                table.column(4).search('').draw(); // รีเซ็ต filter ISO
            } else {
                table.column(4).search(iso).draw(); // Filter by ISO in the 'iso' column
            }
            // เปลี่ยนปุ่มที่เลือกเป็นสีเขียว
            $('.filterISO').removeClass('btn-success').addClass('btn-secondary');
            $(this).removeClass('btn-secondary').addClass('btn-success');
        });
    });
</script>
    
<script>
$(document).on('click', '#selectID', function() {
    let id = $(this).closest('tr').find('td:nth-child(2)').text().trim();
    let textarea = $('textarea');
    let currentText = textarea.val().trim();

    // 🛠️ สลับสถานะปุ่ม "เลือก" → "ลบ"
    if ($(this).hasClass('btn-warning')) {
        if (!currentText.includes(id)) {
            let newText = currentText ? `${currentText}, ${id}` : id;
            textarea.val(newText);

            $(this).removeClass('btn-warning').addClass('btn-info').text('ลบ');
        }
    } else {
        // 🛠️ สลับสถานะปุ่ม "ลบ" → "เลือก"
        let updatedText = currentText
            .split(',')
            .map(item => item.trim())
            .filter(item => item !== id)
            .join(', ');

        textarea.val(updatedText);

        $(this).removeClass('btn-info').addClass('btn-warning').text('เลือก');
    }
});

</script>

    
<script>
$('#saveButton').on('click', function() {
    let dataOut = $('textarea').val().trim();
    
    if (dataOut === "") {
        alert("กรุณาเลือกข้อมูลก่อนบันทึก!");
        return;
    }

    $.post('save_filter_data.php', { dataOut: dataOut }, function(response) {
        alert(response);
    });
});

</script>    

</body>
</html>

<?php
// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>
