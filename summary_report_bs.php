<?php
session_start();

//print_r($_SESSION['currency_data']);

//include_once("chklogin.php");
include_once("connectdb.php");
include_once("queryJSON.php");
include_once("functions.php");

//var_dump($_POST['y'], $_POST['branchID']);exit;

//$y = $_POST['y'];
//$branchID = $_POST['branchID'];
$_POST['Submit'] = "Submit";
$year_th = $_POST['y'] + 543 ;
$_POST['Cmonth'] = sprintf("%02d", $_POST['m']);  // เติม 0 หน้าเดือน 1 หลัก

    $sqlB = "SELECT * FROM `tb2_branch` ORDER BY `tb2_branch`.`branchName` ASC"; 
    $resultB = $conn->query($sqlB);     
    $branches = [];
    while($rowB = $resultB->fetch_assoc()) {
        $branches[$rowB['branchID']] = $rowB['branchName'];
    }

?>

<?php

$branch = $_POST['branchID'];
$y = $_POST['y'];
$m = $_POST['Cmonth'];

$session_id = $_SESSION['ses_id'] ?? '';
if (!$session_id) {
    echo "<p>ไม่พบ session</p>";
    exit;
}

// ดึงข้อมูล buysell จากฐานข้อมูล
$sql = "SELECT buysell FROM session_data_fwd WHERE session_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();
//$conn->close();

if (!$row || !$row['buysell']) {
    echo "<p>ไม่มีข้อมูล buysell</p>";
    exit;
}

$buysell_data = json_decode($row['buysell'], true);
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Chonburi&family=Kanit&family=Pattaya&family=Prompt&family=Srisakdi&display=swap" rel="stylesheet">
<link rel="icon" href="favicon.ico" type="image/x-icon">

<style>
    table {
        font-family: "Prompt", sans-serif;
        font-style: normal;
        font-size: 13px;
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
</style>

<link href="distx/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<script src="distx/js/exceljs.min.js"></script>
<script src="distx/js/FileSaver.min.js"></script>

    <style>
        #timestamp {
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 16px;
            color: #555;
            padding: 10px 0;
        }

        @media print {
            #timestamp {
                display: block; /* โชว์ตอนพิมพ์ */
            }
        }
    </style>
<script>
        function updateTimestamp() {
            const now = new Date();
            const months = [
                "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
                "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
            ];
            const day = now.getDate();
            const month = months[now.getMonth()];
            const year = now.getFullYear() + 543; // แปลงเป็นปี พ.ศ.

            // รูปแบบเวลา 24 ชั่วโมงแบบไทย
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const time = `${hours}:${minutes}:${seconds} น.`;

            document.getElementById("timestamp").innerHTML =
                `พิมพ์เมื่อวันที่ ${day} ${month} ${year} เวลา ${time}`;
        }

        // ดักจับการกด Ctrl+P และอัปเดตเวลาเรียลไทม์ก่อนพิมพ์
        window.addEventListener("beforeprint", updateTimestamp);
    </script>


    <div class="container mt-4">
        <h2 class="mb-4"><i class="fas fa-bookmark"></i> รายงานสรุปการซื้อ/ขายธนบัตรและเช็กเดินทางต่างประเทศ 
      </h2>
    </div>

<div class="container">
    
<?php
if(!isset($_POST['Submit'])){
   exit;
}
?>

    
    
    <button type="button" class="btn btn-warning" name="Export" id="exportBtn"><i class="fas fa-save"></i> ส่งออก (Export)</button>
</div>

<?php
$found = false;

if (isset($buysell_data[$branch][$y][$m])) {
    //$currencies = $buysell_data[$branch][$y][$m];
    //ksort($currencies);
    $found = true;
?>

<div class="d-flex justify-content-center align-items-center">
<table border="1" cellpadding="3" align="center" width="96%" cellspacing="0" class="m-3 mt-4" id="buyTable">
    <thead>
        <tr>
          <th colspan="19" bgcolor="" class="text-center h5" style="height: 84px; font-weight: bold;">รายงานสรุปการซื้อ/ขายธนบัตรและเช็กเดินทางต่างประเทศของบุคคลรับอนุญาต <span class="text-primary">ประจำเดือน <?php echo convertMonthToThai($_POST['Cmonth']);?> <?php echo $year_th;?> สาขา<?php echo $branches[$branch] ?? 'ไม่พบสาขา'; ?></span></th>
        </tr>
      <tr align="center">

          <th bgcolor="#E2EFDA">สกุลเงิน</th>
          <th colspan="2" bgcolor="#E2EFDA">ยอดยกมา</th>
        <th bgcolor="#E2EFDA">ซื้อ</th>
        <th bgcolor="#E2EFDA">ซื้อ</th>
        <th bgcolor="#E2EFDA">ซื้อ</th>
          <th bgcolor="#E2EFDA">ขาย</th>
          <th bgcolor="#E2EFDA">ขาย</th>
          <th bgcolor="#E2EFDA">ขาย</th>
          <th bgcolor="#E2EFDA">ยอดยกไป</th>
          <th bgcolor="#E2EFDA">ยอดยกไป</th>
      </tr>
      <tr align="center">
          <th bgcolor="#E2EFDA">&nbsp;</th>
          <th width="94" bgcolor="#E2EFDA">ธนบัตร</th>
          <th width="122" bgcolor="#E2EFDA">เช็คเดินทาง</th>
          <th bgcolor="#E2EFDA">ธนบัตร</th>
          <th bgcolor="#E2EFDA">เช็คเดินทาง</th>
          <th bgcolor="#E2EFDA">ธนบัตร</th>
          <th width="105" bgcolor="#E2EFDA">ธนบัตร</th>
          <th width="105" bgcolor="#E2EFDA">ธนบัตร</th>
          <th width="168" bgcolor="#E2EFDA">เช็คเดินทาง</th>
          <th width="178" bgcolor="#E2EFDA">ธนบัตร</th>
          <th width="178" bgcolor="#E2EFDA">เช็คเดินทาง</th>
      </tr>
        
    </thead>

    <tbody>            
<?php 
$currencies = getCurrencies();
$vals =  $buysell_data[$branch][$y][$m];
    
foreach ($currencies as $currency):  
    $code = $currency['iso'];
    $data = $vals[$code] ?? ['BF' => 0, 'BUY' => 0, 'SELL' => 0]; // ใช้ค่าเริ่มต้นหากไม่พบ
?>
      <tr>

          <td><?php 
                echo isset($currency['iso']) ? $currency['iso'] : "--";  
              ?></td>
          <td align="right"><?php echo number_format($data['BF'] ?? 0, 2);?></td>
          <td align="right">0.00</td>
          <td align="right"><?php echo number_format($data['BUY'] ?? 0, 2);?></td>
        <td align="right">0.00</td>
        <td align="right">0.00</td>
          <td align="right"><?php echo number_format($data['SELL'] ?? 0, 2);?></td>
          <td align="right">0.00</td>
        <td align="right">0.00</td>
          <td align="right" class="sales">
          <?php 
    $CF = $data['BF']+$data['BUY']-$data['SELL'];
    echo number_format($CF ?? 0, 2); ?>
          </td>
          <td align="right" class="sales">0.00</td>
      </tr>
    <?php endforeach; ?>
        
    </tbody>
</table>
<?php
}

if (!$found) {
    echo "<center><p>ไม่พบข้อมูลสำหรับสาขา $branch ปี $y เดือน $m ต้องกลับไปทำการประมวลผลก่อน!!!</p></center>";
}
?>
</div>

<div>
<?php
$sql3 = "SELECT * FROM session_data_fwd WHERE session_id='{$_SESSION['ses_id']}' ";
$result3 = $conn->query($sql3);
$row3 = $result3->fetch_assoc(); 
    
$date = $row3['last_update'];

$months = [
    "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
    "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
];

// แยกวัน เดือน ปี เวลา
$day = date("j", strtotime($date));
$month = $months[date("n", strtotime($date)) - 1];
$year = date("Y", strtotime($date)) + 543; // แปลง ค.ศ. เป็น พ.ศ.
$time = date("H:i:s", strtotime($date));

// แสดงผล
echo "<font color='#555555'><center>ข้อมูลอัปเดตล่าสุด วันที่ $day $month $year เวลา $time น.</center></font>";
    
$conn->close();
?>
</div>

<div id="timestamp"></div>

    <script>
        document.getElementById('exportBtn').addEventListener('click', function() {
            var table = document.getElementById('buyTable');
            var workbook = new ExcelJS.Workbook();
            var worksheet = workbook.addWorksheet('Sheet1');

            for (var R = 0; R < table.rows.length; ++R) {
                var row = worksheet.addRow(Array.from(table.rows[R].cells).map(cell => cell.innerText));
                
                for (var C = 0; C < table.rows[R].cells.length; ++C) {
                    var cell = table.rows[R].cells[C];
                    var excelCell = row.getCell(C + 1);

                    var bgColor = window.getComputedStyle(cell).backgroundColor;
                    if (bgColor) {
                        excelCell.fill = {
                            type: 'pattern',
                            pattern: 'solid',
                            fgColor: { argb: rgbToHex(bgColor) }
                        };
                    }
                    
                    // เพิ่มเส้นขอบให้กับเซลล์
                    excelCell.border = {
                        top: { style: 'thin' },
                        left: { style: 'thin' },
                        bottom: { style: 'thin' },
                        right: { style: 'thin' }
                    };
                }
            }

            // ซ่อนเส้นขอบในช่วงเซลล์ F2 ถึง N5
//            for (var R = 2; R <= 5; ++R) {
//                for (var C = 6; C <= 14; ++C) {
//                    worksheet.getRow(R).getCell(C).border = {
//                        top: null,
//                        left: null,
//                        bottom: null,
//                        right: null
//                    };
//                }
//            }
            
// ผสานเซลล์ 
worksheet.mergeCells('A1:K1');
worksheet.mergeCells('B2:C2');
worksheet.mergeCells('D2:F2');
worksheet.mergeCells('G2:I2');
worksheet.mergeCells('J2:K2');
worksheet.mergeCells('A2:A3');
            
            workbook.xlsx.writeBuffer().then(function(buffer) {
                saveAs(new Blob([buffer], {type: "application/octet-stream"}), 'summary_report.xlsx');
            });
        });

        function rgbToHex(rgb) {
            var result = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
            return result ? 
                "FF" + ("0" + parseInt(result[1], 10).toString(16)).slice(-2).toUpperCase() +
                ("0" + parseInt(result[2], 10).toString(16)).slice(-2).toUpperCase() +
                ("0" + parseInt(result[3], 10).toString(16)).slice(-2).toUpperCase()
                : "FFFFFF";
        }
    </script>
