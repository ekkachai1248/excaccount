<?php
session_start();

ini_set('max_execution_time', 900);

//print_r($_SESSION['currency_data']);

//include_once("chklogin.php");
include_once("connectdb.php");
include_once("queryJSON.php");
include_once("functions.php");

//var_dump($_POST['m'], $_POST['y'], $_POST['branchID']);exit;
$_POST['Cmonth'] = sprintf("%02d", $_POST['m']);  // เติม 0 หน้าเดือน 1 หลัก
$_POST['Cyear'] = $_POST['y'];
$_POST['Submit'] = "Submit";
$dd = $_POST['Cyear']."-".$_POST['Cmonth'];
$year_th = $_POST['y'] + 543 ;
$branchID = $_POST['branchID'];
//$branchID = 7 ; //$_POST['branchID']
//var_dump($dd);exit;

//$dateDD = DateTime::createFromFormat('Y-m', $dd); // สร้าง DateTime Object
//$dateDD->modify('-1 month'); // ลบ 1 เดือน
//echo $dateDD->format('Y-m'); // ผลลัพธ์: 2023-01

    $sqlB = "SELECT * FROM `tb2_branch` ORDER BY `tb2_branch`.`branchName` ASC"; 
    $resultB = $conn->query($sqlB);     
    $branches = [];
    while($rowB = $resultB->fetch_assoc()) {
        $branches[$rowB['branchID']] = $rowB['branchName'];
    }


$sql3 = "SELECT * FROM brought_forward WHERE branchID = ?";
$stmt3 = $conn->prepare($sql3);
$stmt3->bind_param("i", $branchID);
$stmt3->execute();
$result3 = $stmt3->get_result();
 
if ($result3->num_rows == 0) {
    echo "ไม่พบยอดยกมาของ สาขา{$branches[$branchID]} !!! ";
    exit;
}
?>

<?php
// ดึงข้อมูลจากตาราง filter_data_out สำหรับ id ที่ต้องการดึงออก ไม่นำไปประมวลผล
$sql2 = "SELECT * FROM filter_data_out ORDER BY created_at DESC LIMIT 1";
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

<div id="loading" class="mt-5 img-fluid" align="center">
    <img src="distx/img/processing2.gif" alt="Processing...">
</div>

<div id="content" ><!-- style="display: none;" -->
    
<input type="hidden" id="month" value="<?php echo $_POST['Cmonth'] ?? ''; ?>">
<input type="hidden" id="year" value="<?php echo $_POST['Cyear'] ?? ''; ?>">

    <div class="container mt-4">
        <h2 class="mb-4"><i class="fas fa-bookmark"></i> รายงาน<span class="text-primary">การซื้อ</span>ธนบัตรและเช็คเดินทางต่างประเทศ</h2>
    </div>

<div class="container">
    
<?php
if(!isset($_POST['Submit'])){
   exit;
}
    
// Query หาจำนวนที่ค้นพบ
$sqlN = "
SELECT
	ct.id,
	ct.customerID,
	ct.tran_status,
	ct.receipt_id,
	c.`name`,
	ct.created,
	c.personalID,
	c.country,
	c.login,
	c.cusStatus,
	c.cusType,
	ctd.tranID,
	ts.iso,
	ts.note,
	ts.amountOUT,
	ts.amountIN,
	ts.rateAsset,
	ts.rate,
	tl.branchID,
	tl.`status`
FROM
	tb2_counter_transaction AS ct
	INNER JOIN tb2_customer AS c ON ct.customerID = c.id
	INNER JOIN tb2_counter_transaction_detail AS ctd ON ct.id = ctd.cTranID
	INNER JOIN tb2_transaction_stock AS ts ON ctd.tranID = ts.tranID
	INNER JOIN tb2_transaction_log AS tl ON ctd.tranID = tl.id 
WHERE
	ct.created LIKE '{$dd}%' 
	AND tl.branchID = '{$branchID}' 
	AND tl.tran_status = 2 
	AND ts.note <> 1 
	AND tl.`status` = 1 
	AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL', 'INR')
    AND ct.id NOT IN ({$dataOut})
ORDER BY
	ts.iso ASC,
	ct.created ASC

";  // limit 300  
    // tb2_transaction_log.branchID = '7' = หนองคาย
    // tb2_transaction_log.tran_status = '2' : 2=buy 4=sell
    
    /* SUM( ts.note * ts.amountIN ) AS totalAmount
       SUM( ROUND(ts.note * ts.amountIN * ts.rate, 2) ) AS totalValue
       GROUP BY
	    ct.id
    */
    
    $resultN = $conn->query($sqlN);
    $num = $resultN->num_rows;
?>
    ค้นพบ <span class="text-success"><?=number_format($num,0);?></span> รายการ
    
    <button type="button" class="btn btn-warning" name="Export" id="exportBtn"><i class="fas fa-save"></i> ส่งออก (Export)</button>
</div>
<?php if($num==0){exit;}?>

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
        ct.created LIKE '{$dd}%' 
        AND tl.branchID = '{$branchID}' 
        AND tl.tran_status = 2 
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
    
// เพิ่ม AED และ SEK เฉพาะถ้ายังไม่มีในอาร์เรย์
foreach ([
    'AED', 'SEK', 'MOP', 'IDR', 'MMK', 'QAR', 'ILS', 'LAK', 'OMR',
    'KRW', 'INR', 'NOK', 'PHP', 'DKK', 'AUD', 'ZAR', 'VND', 'CNY',
    'AFN', 'ALL', 'AMD', 'ARS', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN',
    'BHD', 'BIF', 'BND', 'BOB', 'BRL', 'BYN', 'CAD', 'CHF', 'CLP',
    'COP', 'CZK', 'DJF', 'DOP', 'DZD', 'EGP', 'EUR', 'GBP', 'GEL',
    'GHS', 'GIP', 'HKD', 'HRK', 'HUF', 'IQD', 'IRR', 'JMD', 'JOD',
    'JPY', 'KES', 'KWD', 'KZT', 'LBP', 'LKR', 'MAD', 'MDL', 'MKD',
    'MNT', 'MXN', 'MYR', 'NGN', 'NPR', 'NZD', 'PEN', 'PKR', 'PLN',
    'PYG', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SGD', 'SOS', 'SYP',
    'THB', 'TMT', 'TND', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX',
    'USD', 'UYU', 'UZS', 'XAF', 'XCD', 'XOF', 'XPF', 'YER', 'ZMW'
] as $extraIso) {
    if (!in_array($extraIso, $isoArray)) {
        $isoArray[] = $extraIso;
    }
}
sort($isoArray);   
//var_dump($isoArray);exit;
?>

<div class="d-flex justify-content-center align-items-center">
<table border="1" cellpadding="3" align="center" width="96%" cellspacing="0" class="m-3 mt-4" id="buyTable">
    <thead>
        <tr>
          <th colspan="17" bgcolor="" class="text-center h5" style="height: 84px; font-weight: bold;">รายงานการซื้อธนบัตรและเช็คเดินทางต่างประเทศของบุคคลรับอนุญาต ( <?php echo $branches[$branchID] ?? 'ไม่พบสาขา'; ?> ) ประจำเดือน <?php echo convertMonthToThai($_POST['Cmonth']);?> <?php echo $year_th;?></th>
        </tr>
      <tr align="center">
          <th bgcolor="#E2EFDA">วันที่</th>
          <th bgcolor="#E2EFDA">สกุลเงิน</th>
          <th width="94" bgcolor="#E2EFDA">ราคา</th>
          <th width="122" bgcolor="#E2EFDA">จำนวน</th>
          <th bgcolor="#E2EFDA">บาท</th>
          <th bgcolor="#E2EFDA">เงินตรา ตปท.</th>
          <th bgcolor="#E2EFDA">บาท</th>
          <th width="105" bgcolor="#E2EFDA">FIFo</th>
          <th width="168" bgcolor="#E2EFDA">ต้นทุนซื้อ</th>
          <th width="178" bgcolor="#E2EFDA">วันที่</th>
          <th bgcolor="#E2EFDA">สกุลเงิน</th>
          <th bgcolor="#E2EFDA">ราคา</th>
          <th width="110" bgcolor="#E2EFDA">จำนวน</th>
          <th width="120" bgcolor="#E2EFDA">บาท</th>
          <th width="71" bgcolor="#E2EFDA">จำนวน</th>
          <th width="82" bgcolor="#E2EFDA">บาท</th>
          <th width="77" bgcolor="#E2EFDA">กำไร/ขาดทุน</th>
        </tr>
        
    </thead>

    <tbody>            
<?php
//$isoArray = array("AUD", "CAD");  // Test 2 iso
//$isoArray = array("AUD", "CAD");  // , "CHF", "CNY", "EUR", "GBP", "JPY", "USD"
foreach ($isoArray as $currency){
    
$get_sum_out[$currency] = get_sum_out($branchID, $currency, $dd); // get_sum_out('7', $currency, $dd)
    
if($dd == "2025-01"){
    //ยอดยกมา ใส่ มค        
    $sqlF = "SELECT * FROM brought_forward WHERE branchID='{$branchID}' AND iso='{$currency}' AND yearforward LIKE '{$_POST['Cyear']}-{$_POST['Cmonth']}' ORDER BY id ASC";
    $resultF = $conn->query($sqlF);
    
    while($rowF = $resultF->fetch_assoc()) {   
        
        // เก็บยอดยกไป ใส่ใน $_SESIONS แบบอาเรย์
        if ($get_sum_out[$currency] < $rowF['totalAmount']){
            $bg = ""; // $bg = "#FFF6AD"; ไฮไลท์ bg สีเหลืองสำหรับยอดยกไป
            $class_name = "value_go_{$currency}";
        } else {
            $bg = "";
            $class_name = "";
        }

?>
      <tr bgcolor="<?=$bg;?>" class="<?=$class_name;?>">
          <td align="center">&nbsp;</td>
          <td><span style="color: #A70000">ยอดยกมา</span></td>
          <td align="right"><?php 
                $rateF = $rowF['rate'];
                //echo sprintf('%g', $rateF);
                echo $rateF;
              ?></td>
          <td align="right" class="BF_<?php echo $currency;?>"><?php 
                echo number_format($rowF['totalAmount'],2);        
              ?></td>
          <td align="right"><?php 
                echo number_format($rowF['totalValue'],2); 
              ?></td>
          <td align="right" class="col6_<?php echo $currency;?>">
          <?php
$get_sum_out[$currency] -= $rowF['totalAmount'];
       
if ($get_sum_out[$currency] < $rowF['totalAmount']){
   
    if ($get_sum_out[$currency] < 0){
        $first_val = $rowF['totalAmount'] - abs($get_sum_out[$currency]);
        
        if ($first_val > 0){
            $amount = $rateF * abs($get_sum_out[$currency]);
            echo number_format($rowF['totalAmount']-$first_val, 2);
            //echo "<br><font color='gray'>" . number_format($get_sum_out[$currency], 2)."</font>";
        } else {
            $amount = $rateF * $rowF['totalAmount'];
            echo number_format($rowF['totalAmount'], 2);
            //echo "<br><font color='gray'>" . number_format($get_sum_out[$currency], 2)."</font>";
        }
    }  
    
} else {
    $amount = $rateF * $rowF['totalAmount'];
    //echo number_format($rowF['totalAmount'], 2);
}
            ?>
          </td>
          <td align="right" class="col7_<?php echo $currency;?>">
          <?php                          
        if ($get_sum_out[$currency] < 0){
            if(abs($get_sum_out[$currency]) < $rowF['totalAmount']) {
                echo number_format(abs($get_sum_out[$currency]*$rowF['rate']), 2);
            } else {
                echo number_format($rowF['totalAmount']*$rowF['rate'],2);
            }
        }
?>
          </td>
          <td align="right" class="fifo_a_<?php echo $currency;?>">           
<?php 
    //var_dump($rowF['totalAmount']);
    //$get_sum_out[$currency] -= $rowF['totalAmount'];
    
    if ($get_sum_out[$currency] < 0){
        $first_val = $rowF['totalAmount'] - abs($get_sum_out[$currency]);
        
        if ($first_val > 0){
        echo number_format($first_val, 2);
        //echo "<br><font color='gray'>" . number_format($get_sum_out[$currency], 2)."</font>";
        }
    }  else {
        echo number_format($rowF['totalAmount'], 2);
        //echo "<br><font color='gray'>" . number_format($get_sum_out[$currency], 2)."</font>";
    }         
?>              
            </td>
          <td align="right" class="purchase_cost_a_<?php echo $currency;?>">
<?php 
if ($get_sum_out[$currency] < 0){
    if ($first_val > 0){
        echo number_format($first_val * $rateF,2) ;
    }
} else {
    echo number_format($rowF['totalValue'],2);
}
?>
            </td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
<?php 
    } // end ยอดยกมา 
} else {
    
// ยอดยกมา เดือนต่อเดือนถัดไป

$dateDD = DateTime::createFromFormat('Y-m', $dd); // สร้าง DateTime Object
$dateDD->modify('-1 month'); // ลบ 1 เดือน
//echo $dateDD->format('Y-m'); // ผลลัพธ์: 2023-01
    
/////////// วนลูปแสดงข้อมูลยอดยกมาที่ไม่ใช่ของเดือน 2023-01
    
$currency_data = $_SESSION['currency_data'] ?? [];
$data = $currency_data[$branchID][$dateDD->format('Y-m')][$currency] ?? [];
//var_dump($data);
    
foreach ($data as $entry){
    
   $get_sum_out[$currency] -= $entry['amount']; 
    
        // เก็บยอดยกไป ใส่ใน $_SESIONS แบบอาเรย์
        if ($get_sum_out[$currency] < 0){
            $bg = ""; // $bg = "#FFF6AD"; ไฮไลท์ bg สีเหลืองสำหรับยอดยกไป 
            $class_name = "value_go_{$currency}";
        } else {
            $bg = "";
            $class_name = "";
        }
?>
        <tr bgcolor="<?=$bg;?>" class="<?=$class_name?>">
          <td align="center">&nbsp;</td>
          <td>ยอดยกมา</td>
          <td align="right"><?php echo $entry['rate']; //echo number_format($entry['rate'], 2); ?></td>
          <td align="right" class="BF_<?php echo $currency;?>"><?php echo number_format($entry['amount'], 2); ?></td>
          <td align="right">
            <?php
              echo number_format($entry['rate']*$entry['amount'], 2);
              ?>
          </td>
          <td align="right" class="col6_<?php echo $currency;?>">
<?php
                          
        if ($get_sum_out[$currency] < 0){
            if(abs($get_sum_out[$currency]) < $entry['amount']) {
                echo number_format(abs($get_sum_out[$currency]), 2);
            } else {
                echo number_format($entry['amount'],2);
            }
        }
?>
            </td>
          <td align="right" class="col7_<?php echo $currency;?>">
<?php                          
        if ($get_sum_out[$currency] < 0){
            if(abs($get_sum_out[$currency]) < $entry['amount']) {
                echo number_format(abs($get_sum_out[$currency]*$entry['rate']), 2);
            } else {
                echo number_format($entry['amount']*$entry['rate'],2);
            }
        }
?>            
            </td>
          <td align="right" class="fifo_a_<?php echo $currency;?>">
<?php 

    
    if ($get_sum_out[$currency] < 0){
        $first_val = $entry['amount'] - abs($get_sum_out[$currency]);
        
        if ($first_val > 0){
        echo number_format($first_val, 2);
        //echo "<br><font color='gray'>" . number_format($get_sum_out[$currency], 2)."</font>";
        }
    }  else {
        echo number_format($entry['amount'], 2);
        //echo "<br><font color='gray'>" . number_format($get_sum_out[$currency], 2)."</font>";
    }         
?>            
            </td>
          <td align="right" class="purchase_cost_a_<?php echo $currency;?>">
<?php 
if ($get_sum_out[$currency] < 0){
    if ($first_val > 0){
        echo number_format($first_val * $entry['rate'],2) ;
    }
} else {
    echo number_format($entry['rate']*$entry['amount'],2);
}
?>            </td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td>&nbsp;</td>
        </tr>    
<?php 
        } // end foreach ($data as $entry) 
    //} // end foreach ($currency_data ...
} // end if($dd == "2023-01") 
?>      

        
<?php
$sql = "
SELECT
	ct.id,
	ct.customerID,
	ct.tran_status,
	ct.receipt_id,
	c.`name`,
	ct.created,
	c.personalID,
	c.country,
	c.login,
	c.cusStatus,
	c.cusType,
	ctd.tranID,
	ts.iso,
	ts.note,
	ts.amountOUT,
	ts.amountIN,
	ts.rateAsset,
	ts.rate,
	tl.branchID,
	tl.`status`,
    SUM( ts.note * ts.amountIN ) AS totalAmount,
    SUM( ROUND(ts.note * ts.amountIN * ts.rate, 2) ) AS totalValue
FROM
	tb2_counter_transaction AS ct
	INNER JOIN tb2_customer AS c ON ct.customerID = c.id
	INNER JOIN tb2_counter_transaction_detail AS ctd ON ct.id = ctd.cTranID
	INNER JOIN tb2_transaction_stock AS ts ON ctd.tranID = ts.tranID
	INNER JOIN tb2_transaction_log AS tl ON ctd.tranID = tl.id 
WHERE
	ct.created LIKE '{$dd}%' 
	AND tl.branchID = '{$branchID}' 
	AND tl.tran_status = 2 
	AND ts.note <> 1 
	AND tl.`status` = 1 
	AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL', 'INR')
    AND ts.iso = '{$currency}'
    AND ct.id NOT IN ({$dataOut}) 
    -- 59932	2023-04-12 19:18:41	CAD	24.9 150
    -- 60547	2023-04-22 15:53:11	CAD	24.85	80
GROUP BY
	    ct.id
ORDER BY
	ts.iso ASC,
	ct.created ASC

";  // limit 300  
    // tb2_transaction_log.branchID = '7' = หนองคาย
    // tb2_transaction_log.tran_status = '2' : 2=buy 4=sell
    
    /* SUM( ts.note * ts.amountIN ) AS totalAmount,
       SUM( ROUND(ts.note * ts.amountIN * ts.rate, 2) ) AS totalValue
       GROUP BY
	    ct.id
    */
    
    $result = $conn->query($sql);

    
//$brought_forward = array();
    
    while($row = $result->fetch_assoc()) { 
     
        // เก็บยอดยกไป ใส่ใน $_SESIONS แบบอาเรย์
        if ($get_sum_out[$currency] < $row['totalAmount']){
            $bg = ""; // $bg = "#FFF6AD"; ไฮไลท์ bg สีเหลืองสำหรับยอดยกไป
            $class_name = "value_go_{$currency}";
        } else {
            $bg = "";
            $class_name = "";
        }
?>        
        <tr bgcolor="<?=$bg;?>" class="<?=$class_name?>">
          <td align="center">
<?php
// แปลงวันที่        
$dateCr = strtotime($row['created']);
$formattedDate = date('j/n', $dateCr) . '/' . ((date('Y', $dateCr) + 543) % 100);
echo $formattedDate ;
?>     
            </td>
          <td>
              <?php
                $key1 = $row['iso'];
                $res1 = searchCurrencyByKey($key1);
                echo isset($res1['data1']) ? $res1['data1'] : "--";
              ?>
          </td>
          <td align="right">
              <?php 
                $rate = $row['rate'];
                //echo sprintf('%g', $rate);
                echo $rate;
              ?>
            </td>
          <td align="right" class="col4_<?php echo $currency;?>">
              <?php 
                echo number_format($row['totalAmount'],2);
              ?>
            </td>
          <td align="right" class="col5_<?php echo $currency;?>">
              <?php 
                echo number_format($row['totalValue'],2); 
              ?>
            </td>
          <td align="right" class="col6_<?php echo $currency;?>">
              
<?php
$get_sum_out[$currency] -= $row['totalAmount'];
       
if ($get_sum_out[$currency] < $row['totalAmount']){
   
    if ($get_sum_out[$currency] < 0){
        $first_val = $row['totalAmount'] - abs($get_sum_out[$currency]);
        
        if ($first_val > 0){
            $amount = $rate * abs($get_sum_out[$currency]);
            echo number_format($row['totalAmount']-$first_val, 2);
            //echo "<br><font color='gray'>" . number_format($get_sum_out[$currency], 2)."</font>";
        } else {
            $amount = $rate * $row['totalAmount'];
            echo number_format($row['totalAmount'], 2);
            //echo "<br><font color='gray'>" . number_format($get_sum_out[$currency], 2)."</font>";
        }
    }  
    
} else {
    $amount = $rate * $row['totalAmount'];
    //echo number_format($row['totalAmount'], 2);
}
   
// ส่งยอดยกมาไปต่อเดือนถัดไป
//    $brought_forward[$currency][] = array(
//        "id" => $row['id'],
//        "iso" => $currency,
//        "yearforward" => "{$dd}",
//        "rate" => $rate,
//        "amount" => $currentAmount ?? 0,
//        "total_sum" => $totalValueNow ?? 0 
//    );

            ?>
              
            </td>
          <td align="right" class="col7_<?php echo $currency;?>">
<?php
    if ($get_sum_out[$currency] < $row['totalAmount']){
        if ($get_sum_out[$currency] < 0){
            echo number_format($amount, 2);
        }
    }
?>
          </td>
          <td align="right" class="fifo_b_<?php echo $currency;?>">
<?php 
    //$get_sum_out[$currency] -= $row['totalAmount'];
    
    if ($get_sum_out[$currency] < 0){
        $first_val = $row['totalAmount'] - abs($get_sum_out[$currency]);
        
        if ($first_val > 0){
        echo number_format($first_val, 2);
        //echo "<br><font color='gray'>" . number_format($get_sum_out[$currency], 2)."</font>";
        }
    }  else {
        echo number_format($row['totalAmount'], 2);
        //echo "<br><font color='gray'>" . number_format($get_sum_out[$currency], 2)."</font>";
    }         
?>
          </td>
          <td align="right" class="purchase_cost_b_<?php echo $currency;?>">
<?php 
if ($get_sum_out[$currency] < 0){
    if ($first_val > 0){
        echo number_format($first_val * $rate,2) ;
    }
} else {
    echo number_format($row['totalValue'],2);
}
?>
          </td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
<?php } // end while ?>
        <tr>
          <td align="center">&nbsp;</td>
          <td>&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="right"><strong><div id="col4_sum_<?php echo $currency;?>"><span>0.00</span></div></strong></td>
          <td align="right"><strong><div id="col5_sum_<?php echo $currency;?>"><span>0.00</span></div></strong></td>
          <td align="right"><strong><div id="col6_sum_<?php echo $currency;?>"><span>0.00</span></div></strong></td>
          <td align="right"><strong><div id="col7_sum_<?php echo $currency;?>"><span>0.00</span></div></strong></td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td>&nbsp;</td>
        </tr>

<?php
    $sqlAdjSell = "SELECT * FROM `adjust_sell` WHERE `a_iso`='{$currency}' AND `a_date` LIKE '{$dd}%' AND `a_branch`='{$branchID}' AND  `a_active`='1' ";
    $resultAdjSell = $conn->query($sqlAdjSell); 
    while($rowAdjSell = $resultAdjSell->fetch_assoc()) {           
?>
<tr>
          <td align="center">&nbsp;</td>
          <td>&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td align="center"><?php
// แปลงวันที่        
$dateAdjSell = strtotime($rowAdjSell['a_date']);
$formattedDateAdjSell = date('j/n', $dateAdjSell) . '/' . ((date('Y', $dateAdjSell) + 543) % 100);
echo $formattedDateAdjSell ;
?></td>
          <td><?php
                $key1 = $rowAdjSell['a_iso'];
                $res1 = searchCurrencyByKey($key1);
                echo isset($res1['data1']) ? $res1['data1'] : "--";
              ?></td>
          <td align="right"><?php echo $rowAdjSell['a_rate']; ?></td>
          <td align="right" class="col13_<?php echo $currency;?>"><?php echo number_format($rowAdjSell['a_amount'],2); ?></td>
          <td align="right" class="col14_<?php echo $currency;?>"><?php echo number_format($rowAdjSell['a_total'],2); ?></td>
          <td>&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
<?php } ?>      
        
<?php
///////////// out ///////////////   
$sqlOut = "
SELECT
	ct.id,
	ct.customerID,
	ct.tran_status,
	ct.receipt_id,
	c.`name`,
	ct.created,
	c.personalID,
	c.country,
	c.login,
	c.cusStatus,
	c.cusType,
	ctd.tranID,
	ts.iso,
	ts.note,
	ts.amountOUT,
	ts.amountIN,
	ts.rateAsset,
	ts.rate,
	tl.branchID,
	tl.`status`,
    SUM( ts.note * ts.amountOUT ) AS totalAmount,
	SUM( ROUND(ts.note * ts.amountOUT * ts.rate, 2) ) AS totalValue
FROM
	tb2_counter_transaction AS ct
	INNER JOIN tb2_customer AS c ON ct.customerID = c.id
	INNER JOIN tb2_counter_transaction_detail AS ctd ON ct.id = ctd.cTranID
	INNER JOIN tb2_transaction_stock AS ts ON ctd.tranID = ts.tranID
	INNER JOIN tb2_transaction_log AS tl ON ctd.tranID = tl.id 
WHERE
	ct.created LIKE '{$dd}%' 
	AND tl.branchID = '{$branchID}' 
	AND tl.tran_status = 4 
	AND ts.note <> 1 
	AND tl.`status` = 1 
	AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL', 'INR')
    AND ts.iso = '{$currency}'
    AND ct.id NOT IN ({$dataOut})
GROUP BY
        ct.id
ORDER BY
	ts.iso ASC,
	ct.created ASC
";  // limit 300  
    // tb2_transaction_log.branchID = '7' = หนองคาย
    // tb2_transaction_log.tran_status = '2' : 2=buy 4=sell  
    // AND (ct.customerID != '1621' OR ct.customerID != '29466')
    $resultOut = $conn->query($sqlOut); 
    while($rowOut = $resultOut->fetch_assoc()) {   
?>
        <tr>
          <td align="center">&nbsp;</td>
          <td>&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td align="center"><?php
// แปลงวันที่        
$dateCrOut = strtotime($rowOut['created']);
$formattedDateOut = date('j/n', $dateCrOut) . '/' . ((date('Y', $dateCrOut) + 543) % 100);
echo $formattedDateOut ;
?></td>
          <td><?php
                $key1 = $rowOut['iso'];
                $res1 = searchCurrencyByKey($key1);
                echo isset($res1['data1']) ? $res1['data1'] : "--";
              ?></td>
          <td align="right"><?php 
                $rate = $rowOut['rate'];
                //echo sprintf('%g', $rate);
                echo $rate;
              ?></td>
          <td align="right" class="col13_<?php echo $currency;?>"><?php 
        
            if($rowOut['iso']=='AED' and $rowOut['id']=='76425'){
                $rowOut['totalAmount'] = 5;  // จากเดิม 25 ให้เหลือ 5
                $rowOut['totalValue'] = $rate * 5;
            } else {
                $rowOut['totalAmount'] = $rowOut['totalAmount'];
                $rowOut['totalValue'] = $rowOut['totalValue'] ;
            }
        
                echo number_format($rowOut['totalAmount'],2);
                @$sumAmountOut[$currency] += $rowOut['totalAmount'];
              ?></td>
          <td align="right" class="col14_<?php echo $currency;?>"><?php 
                echo number_format($rowOut['totalValue'],2); 
              ?></td>
          <td>&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
    <?php } // end while OUT ?>
        <tr>
          <td align="center">&nbsp;</td>
          <td>&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td bgcolor="#A2E1FD" align="right"><strong><div id="fifo_sum_<?php echo $currency;?>"><span>0.00</span></div></strong></td>
          <td bgcolor="#A2E1FD" align="right"><strong><div id="purchase_cost_sum_<?php echo $currency;?>"><span>0.00</span></div></strong></td>
          <td bgcolor="#A2E1FD" align="center">&nbsp;</td>
          <td bgcolor="#A2E1FD">&nbsp;</td>
          <td bgcolor="#A2E1FD" align="right">&nbsp;</td>
          <td bgcolor="#A2E1FD" align="right"><strong><div id="col13_sum_<?php echo $currency;?>"><span>0.00</span></div></strong></td>
          <td bgcolor="#A2E1FD" align="right"><strong><div id="col14_sum_<?php echo $currency;?>"><span>0.00</span></div></strong></td>
          <td bgcolor="#A2E1FD">&nbsp;</td>
          <td bgcolor="#A2E1FD" align="right">&nbsp;</td>
          <td bgcolor="#A2E1FD" align="right"><strong><div class="profit_<?php echo $currency;?>"><span>0.00</span></div></strong></td>
        </tr>
<?php } // end foreach iso ?>
        <tr>
          <td align="center">&nbsp;</td>
          <td>&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="center">&nbsp;</td>
          <td>&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td align="right">&nbsp;</td>
          <td colspan="2" bgcolor="#FFFE44">รายได้ <?php echo convertMonthToThai($_POST['Cmonth']);?> <?php echo $year_th;?></td>
          <td bgcolor="#FFFE44" align="right"><strong><div class="profit_sum"><span>0.00</span></div></strong></td>
        </tr>

    </tbody>
</table>
</div>

</div> <!-- ปิด <div id="content" style="display: none;"> --> 


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
                saveAs(new Blob([buffer], {type: "application/octet-stream"}), 'buying_acc_report.xlsx');
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
    // เมื่อหน้าเว็บโหลดเสร็จ, ซ่อน "loading" และแสดง "content"
    $(window).on('load', function() {
        $('#loading').fadeOut();  // ซ่อนข้อความ "Loading..."
        $('#content').fadeIn();   // แสดงเนื้อหาหลัก
    });
</script>

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

    // รวมค่าจาก class="sell_amountout_<?php echo $currency;?>"
    $('td[class^="sell_amountout_"]').each(function() {
        let classList = $(this).attr('class').split(' ');
        let currency = "";

        classList.forEach(function(cls) {
            if (cls.startsWith("sell_amountout_")) {
                currency = cls.split('_')[2]; // sell_amountout_AUD → "AUD"
            }
        });

        let value = parseFloat($(this).text().replace(/,/g, '').trim()) || 0;

        if (currency) {
            if (!sellAmountOutGroups[currency]) sellAmountOutGroups[currency] = 0;
            sellAmountOutGroups[currency] += value;
        }
    });

    // อัปเดตค่า col4
    $.each(col4Groups, function(currency, total) {
        let formattedTotal = total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        $(`#col4_sum_${currency} span`).text(formattedTotal);
    });
    // อัปเดตค่า col5
    $.each(col5Groups, function(currency, total) {
        let formattedTotal = total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        $(`#col5_sum_${currency} span`).text(formattedTotal);
    });
    // อัปเดตค่า col6
    $.each(col6Groups, function(currency, total) {
        let formattedTotal = total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        $(`#col6_sum_${currency} span`).text(formattedTotal);
    });
    // อัปเดตค่า col7
    $.each(col7Groups, function(currency, total) {
        let formattedTotal = total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        $(`#col7_sum_${currency} span`).text(formattedTotal);
    });
    
    // อัปเดตค่า col13
    $.each(col13Groups, function(currency, total) {
        let formattedTotal = total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        $(`#col13_sum_${currency} span`).text(formattedTotal);
    });
    // อัปเดตค่า col14
    $.each(col14Groups, function(currency, total) {
        let formattedTotal = total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        $(`#col14_sum_${currency} span`).text(formattedTotal);
    });

    // อัปเดตค่า FIFO
    $.each(currencyGroups, function(currency, total) {
        let formattedTotal = total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        //console.log(`ผลรวมของ ${currency} (fifo):`, formattedTotal);
        $(`#fifo_sum_${currency} span`).text(formattedTotal);
    });

    // อัปเดตค่า Purchase Cost
    $.each(purchaseCostGroups, function(currency, total) {
        let formattedTotal = total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        //console.log(`ผลรวมของ ${currency} (purchase_cost):`, formattedTotal);
        
        if ($(`#purchase_cost_sum_${currency} span`).length) {
            $(`#purchase_cost_sum_${currency} span`).text(formattedTotal);
        } else {
            console.warn(`ไม่พบ <div id="purchase_cost_sum_${currency}"> ในหน้า HTML`);
        }
    });

    // คำนวณกำไรและอัปเดต `<div class="profit_<?php echo $currency;?>">`
    $.each(sellAmountOutGroups, function(currency, sellTotal) {
        let purchaseCost = purchaseCostGroups[currency] || 0;
        let profit = sellTotal - purchaseCost;
        let formattedProfit = profit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");

        //console.log(`กำไรของ ${currency}: ${formattedProfit}`);

        if ($(`.profit_${currency} span`).length) {
            $(`.profit_${currency} span`).text(formattedProfit);
        } else {
            console.warn(`ไม่พบ <div class="profit_${currency}"> ในหน้า HTML`);
        }

        totalProfit += profit; // เพิ่มค่ากำไรแต่ละสกุลเงินเข้า totalProfit
    });
    
    function calculateProfit() {
        $("[id^=col14_sum_]").each(function() {
            let currency = this.id.replace("col14_sum_", "");

            // ตรวจสอบว่า element มีอยู่จริงหรือไม่
            let col14Elem = $("#col14_sum_" + currency + " span");
            let purchaseElem = $("#purchase_cost_sum_" + currency + " span");
            let profitElem = $(".profit_" + currency + " span");

            if (col14Elem.length && purchaseElem.length && profitElem.length) {
                let col14Sum = parseFloat(col14Elem.text().replace(/,/g, "")) || 0;
                let purchaseCostSum = parseFloat(purchaseElem.text().replace(/,/g, "")) || 0;
                let profit = col14Sum - purchaseCostSum;

                // แสดงผลลัพธ์พร้อมคั่นหลักพัน
                profitElem.text(profit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            }
        });
    }

    // เรียกใช้ฟังก์ชันเมื่อหน้าเว็บโหลด
    calculateProfit();

    // ถ้ามีการเปลี่ยนแปลงค่า ให้คำนวณใหม่
    $(document).on("input", "[id^=col14_sum_] span, [id^=purchase_cost_sum_] span", function() {
        calculateProfit();
    });
    
    
    function calculateProfit() {
        let totalProfit = 0; // ตัวแปรเก็บผลรวมของ profit

        $("[id^=col14_sum_]").each(function() {
            let currency = this.id.replace("col14_sum_", "");

            // ตรวจสอบว่า element มีอยู่จริง
            let col14Elem = $("#col14_sum_" + currency + " span");
            let purchaseElem = $("#purchase_cost_sum_" + currency + " span");
            let profitElem = $(".profit_" + currency + " span");

            if (col14Elem.length && purchaseElem.length && profitElem.length) {
                let col14Sum = parseFloat(col14Elem.text().replace(/,/g, "")) || 0;
                let purchaseCostSum = parseFloat(purchaseElem.text().replace(/,/g, "")) || 0;
                let profit = col14Sum - purchaseCostSum;

                // แสดงผลลัพธ์ของแต่ละ currency
                profitElem.text(profit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","));

                // บวกค่า profit รวม
                totalProfit += profit;
            }
        });

        // แสดงผลรวมของกำไรทั้งหมด
        $(".profit_sum span").text(totalProfit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
    }

    // คำนวณเมื่อโหลดหน้าเว็บ
    calculateProfit();

    // คำนวณใหม่เมื่อค่ามีการเปลี่ยนแปลง
    $(document).on("input", "[id^=col14_sum_] span, [id^=purchase_cost_sum_] span", function() {
        calculateProfit();
    });
});
</script>


<script>
$(document).ready(function(){
    let data = {}; //  สร้างออบเจ็กต์เก็บข้อมูล
    let ym = "<?php echo $dd;?>";
    let bid = "<?php echo $branchID;?>";

    $("tr[class^='value_go_']").each(function(){ 
        let cls = $(this).attr("class").split("_")[2]; 
        if (!data[cls]) data[cls] = []; 

        let row = $(this).find("td").map(function(){ 
            return $(this).text().trim().replace(/,/g, ""); // ลบ "," ออกจากตัวเลข
        }).get();

        // ตรวจสอบว่าค่าที่ดึงมามีตัวเลขจริงหรือไม่
        let rateValue = isNaN(row[2]) || row[2] === "" ? null : parseFloat(row[2]);
        let amountValue = isNaN(row[5]) || row[5] === "" ? null : parseFloat(row[5]);

        data[cls].push({ date: row[0], iso: row[1], rate: rateValue, amount: amountValue });
    });
    $.post("save_session.php", { ym: ym, bid: bid, tableData: JSON.stringify(data) });
});

</script>


<script>
$(document).ready(function () {
    let profitValue2 = parseFloat($(".profit_sum").text().replace(/,/g, '')) || 0;
    let month2 = $("#month").val();
    let year2 = $("#year").val();

    $.post("update_profit.php", { 
        profit: profitValue2,
        m: month2,
        y: year2,
        bid: <?php echo $branchID;?>
    })
    .done(function(response) {
        console.log("Response from PHP:", response);
    })
    .fail(function(xhr, status, error) {
        console.error("Error sending data:", error); 
    });
});
</script>


<script>
$(document).ready(function () {
    let buysellData = {};

    function accumulateByClass(prefix, keyName) {
        $(`[class^="${prefix}"]`).each(function () {
            let classList = $(this).attr('class').split(' ');
            classList.forEach(function(cls) {
                if (cls.startsWith(prefix)) {
                    let currency = cls.replace(prefix, "");
                    let value = parseFloat($(this).text().replace(/,/g, '')) || 0;

                    if (!buysellData[currency]) {
                        buysellData[currency] = { BF: 0, BUY: 0, SELL: 0 };
                    }

                    buysellData[currency][keyName] += value;
                }
            }, this);
        });
    }

    // รวมค่าแต่ละประเภท
    accumulateByClass("BF_", "BF");
    accumulateByClass("col4_", "BUY");
    accumulateByClass("col13_", "SELL");

    // แปลงเป็น JSON และเก็บลง hidden input
    let jsonString = JSON.stringify(buysellData);
    $('#buysellData').val(jsonString);

    // ตรวจสอบดูใน console
    console.log(jsonString);
});
</script>

<input type="hidden" name="buysellData" id="buysellData">

<script>
$(document).ready(function () {
    let fwd = $("#buysellData").val();
    let month2 = $("#month").val();
    let year2 = $("#year").val();
    
    $.post("update_buysell.php", { 
        buysell: fwd,
        m: month2,
        y: year2,
        bid: <?php echo $branchID;?>
    })
    .done(function(response) {
        console.log("Response from PHP:", response);
    })
    .fail(function(xhr, status, error) {
        console.error("Error sending data:", error); 
    });
});
</script>


<?php 
//echo "<pre>";
//print_r($brought_forward);
//echo "</pre>"; 
?>
