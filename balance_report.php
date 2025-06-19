<?php
session_start();

//print_r($_SESSION['currency_data']);

//include_once("chklogin.php");
include_once("connectdb.php");
include_once("queryJSON.php");
include_once("functions.php");

//var_dump($_POST['y'], $_POST['branchID']);exit;

$y = $_POST['y'];
$branchID = $_POST['branchID'];
$_POST['Submit'] = "Submit";
$year_th = $_POST['y'] + 543 ;
//var_dump($branchID);exit;

//$dateDD = DateTime::createFromFormat('Y-m', $dd); // สร้าง DateTime Object
//$dateDD->modify('-1 month'); // ลบ 1 เดือน
//echo $dateDD->format('Y-m'); // ผลลัพธ์: 2023-01

    $sqlB = "SELECT * FROM `tb2_branch` ORDER BY `tb2_branch`.`branchName` ASC"; 
    $resultB = $conn->query($sqlB);     
    $branches = [];
    while($rowB = $resultB->fetch_assoc()) {
        $branches[$rowB['branchID']] = $rowB['branchName'];
    }

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
        <h2 class="mb-4"><i class="fas fa-bookmark"></i> รายงานยอดคงเหลือ ณ วันที่ 
        <?php
$thaiMonths = [
    1 => 'มกราคม',
    2 => 'กุมภาพันธ์',
    3 => 'มีนาคม',
    4 => 'เมษายน',
    5 => 'พฤษภาคม',
    6 => 'มิถุนายน',
    7 => 'กรกฎาคม',
    8 => 'สิงหาคม',
    9 => 'กันยายน',
    10 => 'ตุลาคม',
    11 => 'พฤศจิกายน',
    12 => 'ธันวาคม'
];


// คำนวณวันสุดท้ายของปี
$lastDay = new DateTime("last day of December $y");

// แปลงเป็นปี พ.ศ.
$thaiYear = $lastDay->format('Y') + 543; // ปี พ.ศ.

$month = $lastDay->format('n'); // เดือน (1-12)
$monthName = $thaiMonths[$month]; // ชื่อเดือนเป็นภาษาไทย

// แสดงวันที่สุดท้ายของปีพร้อมชื่อเดือนภาษาไทย
echo "31 $monthName $thaiYear"; // ตัวอย่าง: 31 ธันวาคม 2566
            ?>
            
            สาขา<?php echo $branches[$branchID] ?? 'ไม่พบสาขา'; ?>
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

<div class="d-flex justify-content-center align-items-center">
<table border="1" cellpadding="3" align="center" width="96%" cellspacing="0" class="m-3 mt-4" id="buyTable">
    <thead>
        <tr>
          <th colspan="17" bgcolor="" class="text-center h5" style="height: 84px; font-weight: bold;">รายงานยอดคงเหลือ ณ วันที่ <?php echo "31 $monthName $thaiYear";?> สาขา<?php echo $branches[$branchID] ?? 'ไม่พบสาขา'; ?></th>
        </tr>
      <tr align="center">
          <th bgcolor="#E2EFDA">ที่</th>
          <th bgcolor="#E2EFDA">รายการ</th>
          <th width="94" bgcolor="#E2EFDA">จำนวน</th>
          <th width="122" bgcolor="#E2EFDA">จำนวน (บาท)</th>
          <th bgcolor="#E2EFDA">รายได้จากการบริการ</th>
          <th bgcolor="#E2EFDA">กำไร</th>
          <th bgcolor="#E2EFDA">ขาดทุน</th>
          <th width="105" bgcolor="#E2EFDA">กำไรจากอัตราแลกเปลี่ยน</th>
          <th width="168" bgcolor="#E2EFDA">หมายเหตุ</th>
          <th width="178" bgcolor="#E2EFDA">ยอดขาย</th>
        </tr>
        
    </thead>

    <tbody>            
<?php
// หา Currency (iso) ที่ไม่ซ้ำ
$sql1 = "
    SELECT DISTINCT
        ts.iso
    FROM
        tb2_counter_transaction AS ct
        INNER JOIN tb2_counter_transaction_detail AS ctd ON ct.id = ctd.cTranID
        INNER JOIN tb2_transaction_stock AS ts ON ctd.tranID = ts.tranID
        INNER JOIN tb2_transaction_log AS tl ON ctd.tranID = tl.id 
    WHERE
        ct.created LIKE '{$y}%' 
        AND tl.branchID = '{$branchID}' 
        -- AND tl.tran_status = 2 
        AND ts.note <> 1 
        AND tl.`status` = 1 
        AND ts.iso NOT IN ( 'NPR', 'GIP', 'SOS', 'LTL', 'INR', 'SCP') 
    ORDER BY
        ts.iso ASC;
";
$result1 = $conn->query($sql1);
$isoArray = [];
if ($result1->num_rows > 0) {
    while ($row1 = $result1->fetch_assoc()) {
        $isoArray[] = $row1['iso'];
    }
}
        
$sql3 = "SELECT * FROM session_data_fwd WHERE session_id='{$_SESSION['ses_id']}' ";
$result3 = $conn->query($sql3);
$row3 = $result3->fetch_assoc();  
$currency_data = json_decode($row3['data'], true);
 
//print_r($_SESSION['currency_data']);exit; 

$i = 0;

// เก็บผลรวมแต่ละสกุลเงิน
$totalAmount = [];
$totalRateAmount = [];

// แปลงปี ค.ศ. เป็น พ.ศ. (เช่น 2024 -> 67)
$thaiYear = substr(($y + 543), -2); // '67'

// กำหนดรหัสสาขาที่ต้องการ 
$targetBranch = $branchID;

// เริ่มลูปสกุลเงินที่ต้องการหา
foreach ($isoArray as $currency) {
$i++;
    
    // เช็คว่ามีข้อมูลสาขา 7 เท่านั้น
    if (isset($currency_data[$targetBranch])) {
        $branchData = $currency_data[$targetBranch];

        // วนลูปเดือนต่างๆ เช่น "2025-01"
        foreach ($branchData as $month => $monthData) {
            // เช็คว่าสกุลเงินตรงกับที่กำหนดไหม
            if (isset($monthData[$currency])) {
                // วนลูปในแต่ละรายการของสกุลเงินนั้น
                foreach ($monthData[$currency] as $entry) {
                    // แยกวันที่และตรวจสอบปี
                    $dateParts = explode("/", $entry['date']);

                    if (isset($dateParts[2]) && $dateParts[2] == $thaiYear) {
                        // ถ้ายังไม่มีค่า ให้เริ่มต้นที่ 0
                        if (!isset($totalAmount[$currency])) {
                            $totalAmount[$currency] = 0;
                            $totalRateAmount[$currency] = 0;
                        }

                        // บวกค่าจำนวนเงิน
                        $totalAmount[$currency] += $entry['amount'];

                        // คำนวณ (rate * amount)
                        $totalRateAmount[$currency] += $entry['rate'] * $entry['amount'];
                    }
                }
            }
        }
    }
//}


?>
      <tr>
          <td align="center"><?php echo $i; ?></td>
          <td><a href="#" onclick="navigateToReportISO('<?php echo $currency; ?>')" style="cursor: pointer;"><?php 
                $res1 = searchCurrencyByKey($currency);
                echo isset($res1['data1']) ? $res1['data1'] : "--";
                //echo $currency;
              ?></a></td>
          <td align="right"><?php echo number_format($totalAmount[$currency] ?? 0, 2); ?></td>
          <td align="right"><?php echo number_format($totalRateAmount[$currency] ?? 0, 2); ?></td>
          <td align="right">
<?php
//$branchID = 7; // กำหนดรหัสสาขา 7 คือ หนองคาย
//$y = 2024;
    
$sessionId = $_SESSION['ses_id'];

// คำสั่ง SQL เพื่อดึงข้อมูลจากฟิลด์ revenue ที่มีปีและ iso ตรงกัน
$sql = "SELECT revenue FROM session_data_fwd WHERE session_id = '$sessionId'"; 
$result = $conn->query($sql);

if ($result) {
    $data = $result->fetch_assoc();
    if ($data && is_string($data['revenue'])) {
        $revenueData = json_decode($data['revenue'], true);
        
        if (isset($revenueData[$branchID][$y])) {
            $found = false;
            foreach ($revenueData[$branchID][$y] as $entry) {
                if (isset($entry['iso']) && $entry['iso'] === $currency) {
                    echo number_format($entry['revenue'], 2);
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                echo "<font color='red'>0.00</font>";
            }
        } else {
            echo "<font color='red'>0.00</font>";
        }
        
    } else {
        echo "Invalid revenue data.";
    }
} else {
    echo "ไม่พบข้อมูล";
}
?>
        </td>
          <td align="right">&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="center">&nbsp;</td>
          <td align="right" class="sales">
                      <?php
$sessionId = $_SESSION['ses_id'];
// คำสั่ง SQL เพื่อดึงข้อมูลจากฟิลด์ revenue ที่มีปีและ iso ตรงกัน
$sql = "SELECT revenue FROM session_data_fwd WHERE session_id = '$sessionId'"; // ใช้ SQL แบบตรงๆ
$result = $conn->query($sql);
if ($result) {
    $data = $result->fetch_assoc();
    if ($data && is_string($data['revenue'])) {
        $salesData = json_decode($data['revenue'], true);
        $sales = null;
        if (isset($salesData[$branchID][$y])) {
            foreach ($salesData[$branchID][$y] as $entry) {
                if ($entry['iso'] === $currency) {
                    $sales = $entry['sales'];
                    break;
                }
            }
        }
        
        echo $sales !== null ? number_format($sales,2) : "<font color='red'>0.00</font>";
    } else {
        echo "Invalid revenue data.";
    }
} else {
    echo "ไม่พบข้อมูล";
}
              ?>
          </td>
        </tr>
    <?php } ?>     
        
      <tr>
        <td align="center">&nbsp;</td>
        <td>&nbsp;</td>
        <td align="right">&nbsp;</td>
        <td align="right">&nbsp;</td>
        <td align="right">&nbsp;</td>
        <td align="right">&nbsp;</td>
        <td align="right">&nbsp;</td>
        <td align="right">&nbsp;</td>
        <td align="center">&nbsp;</td>
          <td align="right"><strong><div class="total"><span>0.00</span></div></strong></td>
      </tr>
      <tr>
        <td align="center">&nbsp;</td>
        <td align="center">สาขา<?php echo $branches[$branchID] ?? 'ไม่พบสาขา'; echo " ".$year_th;?></td>
        <td align="right">&nbsp;</td>
        <td align="right">&nbsp;</td>
        <td align="right">&nbsp;</td>
        <td align="right">&nbsp;</td>
        <td align="right">&nbsp;</td>
        <td align="right">&nbsp;</td>
        <td align="center">&nbsp;</td>
        <td align="right">&nbsp;</td>
      </tr>
<?php
foreach ($thaiMonths as $key => $value) {    
    //$counter = $key + 1;
    //echo $counter;
?>
      <tr>
        <td align="center"><?php echo ltrim($key, "0");?></td>
        <td>กำไรจากการขายเดือน <a href="#" onclick="navigateToReport(<?php echo $key;?>)" style="cursor: pointer;"><?php echo $value;?></a></td>
        <td align="right" class="profit">
          <?php
//$row3['profit'] = '{"2023":{"02":"6406.25","03":"8449"}';                                     
//$profit_json = $row3['profit'] ?? '{}'; 
$profit_data = json_decode($row3['profit'], true); 
//var_dump($y, $key, str_pad($key, 2, "0", STR_PAD_LEFT));
                   
if (isset($profit_data[$branchID][$y][str_pad($key, 2, "0", STR_PAD_LEFT)])) {
      $profit = $profit_data[$branchID][$y][str_pad($key, 2, "0", STR_PAD_LEFT)];
      echo number_format($profit, 2);
  } else {
      echo "<font color='red'>0.00</font>";
  }
?>
          </td>
        <td align="right">&nbsp;</td>
        <td align="right">&nbsp;</td>
        <td align="right">&nbsp;</td>
        <td align="right">&nbsp;</td>
        <td align="right">&nbsp;</td>
        <td align="center">&nbsp;</td>
        <td align="right">&nbsp;</td>
      </tr>
<?php } ?>
      <tr>
        <td align="center">&nbsp;</td>
        <td>กำไรจากการขายรวม</td>
        <td align="right"><strong><div class="profit_sum"><span>0.00</span></div></strong></td>
        <td align="right">&nbsp;</td>
        <td align="right">&nbsp;</td>
        <td align="right">&nbsp;</td>
        <td align="right">&nbsp;</td>
        <td align="right">&nbsp;</td>
        <td align="center">&nbsp;</td>
        <td align="right">&nbsp;</td>
      </tr>
        
    </tbody>
</table>
</div>

<div>
<?php
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
worksheet.mergeCells('A1:Q1');
//worksheet.mergeCells('A6:R6');
//worksheet.mergeCells('F2:R2');
//worksheet.mergeCells('F3:R3');
//worksheet.mergeCells('F4:R4');
//worksheet.mergeCells('F5:R5');
            
            workbook.xlsx.writeBuffer().then(function(buffer) {
                saveAs(new Blob([buffer], {type: "application/octet-stream"}), 'balance_report.xlsx');
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


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let col4Groups = {};
    let col5Groups = {};
    let col6Groups = {};
    let col7Groups = {};
    let col13Groups = {};
    let col14Groups = {};
    let currencyGroups = {}; // ผลรวมตามสกุลเงินสำหรับ fifo
    let purchaseCostGroups = {}; // ผลรวมตามสกุลเงินสำหรับ purchase_cost
    let sellAmountOutGroups = {}; // ผลรวมตามสกุลเงินสำหรับ sell_amountout
    let totalProfit = 0; // ตัวแปรเก็บกำไรรวม

    // ค้นหาค่าจากตาราง (FIFO & Purchase Cost)
    $('td[class^="col4_"], td[class^="col5_"], td[class^="col6_"], td[class^="col7_"], td[class^="col13_"], td[class^="col14_"], td[class^="fifo_a_"], td[class^="fifo_b_"], td[class^="purchase_cost_a_"], td[class^="purchase_cost_b_"]').each(function() {
        let classList = $(this).attr('class').split(' ');
        let currency = "";

        classList.forEach(function(cls) {
            if (cls.startsWith("col4_") || cls.startsWith("col5_") || cls.startsWith("col6_") || cls.startsWith("col7_") || cls.startsWith("col13_") || cls.startsWith("col14_")) {
                currency = cls.split('_')[1]; // แบ่งข้อมูลจากเครื่องหมาย _
            }
            if (cls.startsWith("fifo_a_") || cls.startsWith("fifo_b_")) {
                currency = cls.split('_')[2]; // fifo_a_AUD → "AUD"
            }
            if (cls.startsWith("purchase_cost_a_") || cls.startsWith("purchase_cost_b_")) {
                currency = cls.split('_')[3]; // purchase_cost_a_AUD → "AUD"
            }
        });

        let value = parseFloat($(this).text().replace(/,/g, '').trim()) || 0;

        if (currency) {            
            // สำหรับ col4
            if (classList.some(cls => cls.startsWith("col4_") )) {
                if (!col4Groups[currency]) col4Groups[currency] = 0;
                col4Groups[currency] += value;
            }

            // สำหรับ col5
            if (classList.some(cls => cls.startsWith("col5_") )) {
                if (!col5Groups[currency]) col5Groups[currency] = 0;
                col5Groups[currency] += value;
            }

            // สำหรับ col6
            if (classList.some(cls => cls.startsWith("col6_") )) {
                if (!col6Groups[currency]) col6Groups[currency] = 0;
                col6Groups[currency] += value;
            }

            // สำหรับ col7
            if (classList.some(cls => cls.startsWith("col7_") )) {
                if (!col7Groups[currency]) col7Groups[currency] = 0;
                col7Groups[currency] += value;
            }

            // สำหรับ col13
            if (classList.some(cls => cls.startsWith("col13_") )) {
                if (!col13Groups[currency]) col13Groups[currency] = 0;
                col13Groups[currency] += value;
            }

            // สำหรับ col14
            if (classList.some(cls => cls.startsWith("col14_") )) {
                if (!col14Groups[currency]) col14Groups[currency] = 0;
                col14Groups[currency] += value;
            }

            // สำหรับ FIFO
            if (classList.some(cls => cls.startsWith("fifo_a_") || cls.startsWith("fifo_b_"))) {
                if (!currencyGroups[currency]) currencyGroups[currency] = 0;
                currencyGroups[currency] += value;
            }

            // สำหรับ Purchase Cost
            if (classList.some(cls => cls.startsWith("purchase_cost_a_") || cls.startsWith("purchase_cost_b_"))) {
                if (!purchaseCostGroups[currency]) purchaseCostGroups[currency] = 0;
                purchaseCostGroups[currency] += value;
            }
        }
    });

    
});
</script>

<script>
$(document).ready(function() {
    var totalSales = 0;
    $(".sales").each(function() {
        totalSales += parseFloat($(this).text().replace(/,/g, '').trim()) || 0;
    });
    $(".total span").text(totalSales.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
    
    
    var totalProfit = 0;
    $(".profit").each(function() {
        var profit = parseFloat($(this).text().replace(/,/g, '')) || 0; // เปลี่ยนเครื่องหมาย , ออกและแปลงเป็นตัวเลข
        totalProfit += profit;
    });
    $(".profit_sum span").text(totalProfit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
});

</script>


<script>
function navigateToReport(month) {
    let year = <?php echo $y;?>;
    let ym = year + '-' + (month < 10 ? '0' + month : month);

    console.log("ปี-เดือน: " + ym);

    const selectedYear = year;
    const christianYear = selectedYear - 543;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'buying_report_a.php';
    form.target = '_blank';

    const yearInput = document.createElement('input');
    yearInput.type = 'hidden';
    yearInput.name = 'y';
    yearInput.value = year;
    form.appendChild(yearInput);

    const monthInput = document.createElement('input');
    monthInput.type = 'hidden';
    monthInput.name = 'm';
    monthInput.value = month;
    form.appendChild(monthInput);

    const branchID = <?php echo isset($branchID) ? json_encode($branchID) : 'null'; ?>;
    const branchInput = document.createElement('input');
    branchInput.type = 'hidden';
    branchInput.name = 'branchID';
    branchInput.value = branchID ;
    form.appendChild(branchInput);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
        
    function navigateToReportISO(iso) {
        const selectedYear = <?php echo $y;?>;
        const christianYear = selectedYear - 543; // แปลง พ.ศ. เป็น ค.ศ.
        
        // สร้างฟอร์มแบบ dynamic สำหรับ POST
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'iso_report.php';
        form.target = '_blank';

        // สร้าง input สำหรับ iso
        const isoInput = document.createElement('input');
        isoInput.type = 'hidden';
        isoInput.name = 'iso';
        isoInput.value = iso;
        form.appendChild(isoInput);

        // สร้าง input สำหรับปี
        const yearInput = document.createElement('input');
        yearInput.type = 'hidden';
        yearInput.name = 'y';
        yearInput.value = selectedYear;
        form.appendChild(yearInput);

        // สร้าง input สำหรับเดือน
        //const monthInput = document.createElement('input');
        //monthInput.type = 'hidden';
        //monthInput.name = 'm';
        //monthInput.value = month;
        //form.appendChild(monthInput);

        // สร้าง input สำหรับ branchID
        const branchID = <?php echo isset($branchID) ? json_encode($branchID) : 'null'; ?>;
        const branchInput = document.createElement('input');
        branchInput.type = 'hidden';
        branchInput.name = 'branchID';
        //branchInput.value = branchID;
        branchInput.value = branchID;
        form.appendChild(branchInput);

        // เพิ่มฟอร์มเข้า DOM และส่งข้อมูล
        document.body.appendChild(form);
        form.submit();

        // ลบฟอร์มออกหลังส่ง
        document.body.removeChild(form);
      }

        
function navigateToBalanceReport(year) {
    const selectedYear = year; // ใช้ปีที่ส่งมาจากคลิก

    // สร้างฟอร์มแบบ dynamic สำหรับ POST
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'balance_report.php';
    form.target = '_blank';  // เปิดในแท็บใหม่

    // สร้าง input สำหรับปี
    const yearInput = document.createElement('input');
    yearInput.type = 'hidden';
    yearInput.name = 'y';  // ชื่อ parameter ที่จะส่งไป
    yearInput.value = selectedYear;  // ค่าเป็นปีที่ส่งมาจากคลิก
    form.appendChild(yearInput);

    // สร้าง input สำหรับ branchID
    const branchID = <?php echo json_encode(isset($_GET['bid']) ? $_GET['bid'] : 7); ?>;
    const branchInput = document.createElement('input');
    branchInput.type = 'hidden';
    branchInput.name = 'branchID';  // ชื่อ parameter สำหรับรหัสสาขา
    branchInput.value = branchID;  // ค่ารหัสสาขาจาก URL หรือ 7 ถ้าไม่มี
    form.appendChild(branchInput);

    // เพิ่มฟอร์มเข้า DOM และส่งข้อมูล
    document.body.appendChild(form);
    form.submit();


    // ลบฟอร์มออกหลังส่ง
    document.body.removeChild(form);
}
</script>

