<?php
//include_once("chklogin.php");
include_once("connectdb.php");
include_once("queryJSON.php");

//var_dump($_POST['m'], $_POST['y'], $_POST['branchID']);exit;
$_POST['Cmonth'] = sprintf("%02d", $_POST['m']);  // เติม 0 หน้าเดือน 1 หลัก
$_POST['Cyear'] = $_POST['y'];
$_POST['Submit'] = "Submit";
$dd = $_POST['Cyear']."-".$_POST['Cmonth']."-";
$year_th = $_POST['y'] + 543 ;

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
	AND tl.branchID = '{$_POST['branchID']}' 
	AND tl.tran_status = 2 
	AND ts.note <> 1 
	AND tl.`status` = 1 
	AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL', 'INR')
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
        AND tl.branchID = '{$_POST['branchID']}' 
        AND tl.tran_status = 2 
        AND ts.note <> 1 
        AND tl.`status` = 1 
        AND ts.iso NOT IN ( 'NPR', 'GIP', 'SOS', 'LTL', 'INR') 
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
?>

<div class="d-flex justify-content-center align-items-center">
<table border="1" cellpadding="3" align="center" width="96%" cellspacing="0" class="m-3 mt-4" id="buyTable">
    <thead>
        <tr>
          <th colspan="17" bgcolor="" class="text-center h5" style="height: 84px; font-weight: bold;">รายงานการซื้อธนบัตรและเช็คเดินทางต่างประเทศของบุคคลรับอนุญาต ( หนองคาย ) ประจำเดือน <?php echo convertMonthToThai($_POST['Cmonth']);?> <?php echo $year_th;?></th>
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
//$isoArray = array("AED", "AUD", "BND", "CAD");  // Test 4 iso
$isoArray = array("AUD" , "CAD", "CNY");  // Test 2 iso
foreach ($isoArray as $currency){
    
if($dd == "2023-01-"){
    //ยอดยกมา ใส่ มค 2566 (2023)        
    $sqlF = "SELECT * FROM brought_forward WHERE iso='{$currency}' AND yearforward LIKE '{$_POST['Cyear']}-{$_POST['Cmonth']}' ORDER BY id ASC";
    $resultF = $conn->query($sqlF);
    $sum_cut_sales = [];
    while($rowF = $resultF->fetch_assoc()) {   
?>
        <tr>
          <td align="center">&nbsp;</td>
          <td>ยอดยกมา</td>
          <td align="right"><?php 
                $rateF = $rowF['rate'];
                echo sprintf('%g', $rateF);
              ?></td>
          <td align="right"><?php 
                echo number_format($rowF['totalAmount'],2);
                //$sumAmountIN_F += $rowF['totalAmount'];
        
                // รวมยอดตัดขาย 
                $sum_cut_sales[$currency] = ($sum_cut_sales[$currency] ?? 0) + $rowF['totalAmount'];
              ?></td>
          <td align="right"><?php 
                echo number_format($rowF['totalValue'],2); 
                //$sumBuyAmountIN_F += $rowF['totalValue'];
              ?></td>
          <td align="right">-</td>
          <td align="right">-</td>
          <td align="right" class="fifo_a_<?php echo $currency;?>"><?php echo number_format($rowF['totalAmount'],2);?></td>
          <td align="right" class="purchase_cost_a_<?php echo $currency;?>"><?php echo number_format($rowF['totalValue'],2);?></td>
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

$dateDD = DateTime::createFromFormat('Y-m-', $dd); // สร้าง DateTime Object
$dateDD->modify('-1 month'); // ลบ 1 เดือน
//echo $dateDD->format('Y-m-'); // ผลลัพธ์: 2023-01-

$sqlT = "
SELECT 
    (SELECT SUM(ts.note * ts.amountOUT) 
     FROM tb2_counter_transaction AS ct
     INNER JOIN tb2_counter_transaction_detail AS ctd ON ct.id = ctd.cTranID
     INNER JOIN tb2_transaction_stock AS ts ON ctd.tranID = ts.tranID
     INNER JOIN tb2_transaction_log AS tl ON ctd.tranID = tl.id
     WHERE ct.created LIKE '{$dateDD->format('Y-m-')}%' 
     AND tl.branchID = '7' 
     AND tl.tran_status = 4 
     AND ts.note <> 1 
     AND tl.status = 1 
     AND ts.iso = '{$currency}' 
     AND ct.customerID NOT IN ('1621', '29466', '29563', '6500')) AS totalBeforeDeduction,

    ((SELECT SUM(ts.note * ts.amountOUT) 
     FROM tb2_counter_transaction AS ct
     INNER JOIN tb2_counter_transaction_detail AS ctd ON ct.id = ctd.cTranID
     INNER JOIN tb2_transaction_stock AS ts ON ctd.tranID = ts.tranID
     INNER JOIN tb2_transaction_log AS tl ON ctd.tranID = tl.id
     WHERE ct.created LIKE '{$dateDD->format('Y-m-')}%' 
     AND tl.branchID = '7' 
     AND tl.tran_status = 4 
     AND ts.note <> 1 
     AND tl.status = 1 
     AND ts.iso = '{$currency}' 
     AND ct.customerID NOT IN ('1621', '29466', '29563', '6500'))     
    - 
    (SELECT SUM(totalAmount) 
     FROM `brought_forward` 
     WHERE `iso` = '{$currency}')) AS totalAfterDeduction;
";
$resultT = $conn->query($sqlT);
$rowT = $resultT->fetch_assoc();
//////////////    
    
$sqlNext = "WITH total_sum AS (
    SELECT
        ct.id,
        ct.created,  
        ts.iso,
        ts.rate,
        SUM(ts.note * ts.amountIN) AS totalAmount
    FROM
        tb2_counter_transaction AS ct
    INNER JOIN tb2_customer AS c ON ct.customerID = c.id
    INNER JOIN tb2_counter_transaction_detail AS ctd ON ct.id = ctd.cTranID
    INNER JOIN tb2_transaction_stock AS ts ON ctd.tranID = ts.tranID
    INNER JOIN tb2_transaction_log AS tl ON ctd.tranID = tl.id
    WHERE
        ct.created LIKE '{$dateDD->format('Y-m-')}%'
        AND tl.branchID = '7'
        AND tl.tran_status = 2
        AND ts.note <> 1
        AND tl.status = 1
        AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL', 'INR')
        AND ts.iso = '{$currency}'
        AND ct.customerID NOT IN ('1621', '29466', '29563')
    GROUP BY
        ct.id, ct.created  
),
running_total AS (
    SELECT
        ts.id,
        ts.iso,
        ts.rate,
        ts.totalAmount,
        ts.created,  
        SUM(ts.totalAmount) OVER (ORDER BY ts.id ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS cumulativeAmount,
        ts.rate * ts.totalAmount AS totalValue
    FROM
        total_sum AS ts
)
SELECT
    ts.id,
    ts.iso,
    ts.rate,
    ts.totalAmount,
    ts.totalValue,
    ts.created  
FROM
    running_total AS ts
WHERE
    ts.cumulativeAmount >= '{$rowT['totalAfterDeduction']}'
ORDER BY
    ts.id;
"; //var_dump($sqlNext);
$resultNext = $conn->query($sqlNext);
$round = 0;
while($rowNext = $resultNext->fetch_assoc()){
    $round++;
?>
        <tr>
          <td align="center">&nbsp;</td>
          <td>ยอดยกมา22</td>
          <td align="right"><?php 
                $rateNext = $rowNext['rate'];
                echo sprintf('%g', $rateNext);
              ?></td>
          <td align="right">
          <?php 
    if($dateDD->format('Y-m-') == "2023-01-"){
        // หาผลรวมของยอดยกมา เฉพาะของ 2023-01
        $sql2 = "SELECT brought_forward.iso, Sum( brought_forward.totalAmount ) AS sum_brought_forward 
        FROM brought_forward 
        WHERE brought_forward.iso = '{$currency}' 
        GROUP BY brought_forward.iso";
        $rs2 = $conn->query($sql2);
        $row2 = $rs2->fetch_assoc();
        
        $sql3 = "WITH total_sum AS (
    SELECT
        ct.id,
        ct.created,  
        ts.iso,
        ts.rate,
        SUM(ts.note * ts.amountIN) AS totalAmount
    FROM
        tb2_counter_transaction AS ct
    INNER JOIN tb2_customer AS c ON ct.customerID = c.id
    INNER JOIN tb2_counter_transaction_detail AS ctd ON ct.id = ctd.cTranID
    INNER JOIN tb2_transaction_stock AS ts ON ctd.tranID = ts.tranID
    INNER JOIN tb2_transaction_log AS tl ON ctd.tranID = tl.id
    WHERE
        ct.created LIKE '2023-01-%'
        AND tl.branchID = '7'
        AND tl.tran_status = 2
        AND ts.note <> 1
        AND tl.status = 1
        AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL', 'INR')
        AND ts.iso = '{$currency}'
        AND ct.customerID NOT IN ('1621', '29466', '29563')
    GROUP BY
        ct.id, ct.created  
),
running_total AS (
    SELECT
        ts.id,
        ts.iso,
        ts.rate,
        ts.totalAmount,
        ts.created,  
        SUM(ts.totalAmount) OVER (ORDER BY ts.id ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS cumulativeAmount,
        ts.rate * ts.totalAmount AS totalValue
    FROM
        total_sum AS ts
)
SELECT 
    SUM(ts.totalAmount) AS total_sum  
FROM 
    running_total AS ts
WHERE 
    ts.cumulativeAmount <= '{$rowT['totalAfterDeduction']}';
";
        $rs3 = $conn->query($sql3);
        $row3 = $rs3->fetch_assoc();        
        
        $first_value = $rowT['totalBeforeDeduction'] - ($row2['sum_brought_forward'] + $row3['total_sum']);
        //echo $first_value;
    } else {
        // ถ้าไม่ใช่ของปี 2023-01   
   $sql4 = "SELECT SUM(ts.note * ts.amountOUT) AS totalAmount
     FROM tb2_counter_transaction AS ct
     INNER JOIN tb2_counter_transaction_detail AS ctd ON ct.id = ctd.cTranID
     INNER JOIN tb2_transaction_stock AS ts ON ctd.tranID = ts.tranID
     INNER JOIN tb2_transaction_log AS tl ON ctd.tranID = tl.id
     WHERE ct.created LIKE '{$dateDD->format('Y-m-')}%' 
     AND tl.branchID = '7' 
     AND tl.tran_status = 4 
     AND ts.note <> 1 
     AND tl.status = 1 
     AND ts.iso = '{$currency}' 
     AND ct.customerID NOT IN ('1621', '29466', '29563', '6500');
";     //var_dump($sql4);
    $rs4 = $conn->query($sql4);
    $row4 = $rs4->fetch_assoc(); 
        
    $sql5 = "WITH total_sum AS (
    SELECT
        ct.id,
        ct.created,  
        ts.iso,
        ts.rate,
        SUM(ts.note * ts.amountIN) AS totalAmount
    FROM
        tb2_counter_transaction AS ct
    INNER JOIN tb2_customer AS c ON ct.customerID = c.id
    INNER JOIN tb2_counter_transaction_detail AS ctd ON ct.id = ctd.cTranID
    INNER JOIN tb2_transaction_stock AS ts ON ctd.tranID = ts.tranID
    INNER JOIN tb2_transaction_log AS tl ON ctd.tranID = tl.id
    WHERE
        ct.created LIKE '{$dateDD->format('Y-m-')}%'
        AND tl.branchID = '7'
        AND tl.tran_status = 2
        AND ts.note <> 1
        AND tl.status = 1
        AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL', 'INR')
        AND ts.iso = '{$currency}'
        AND ct.customerID NOT IN ('1621', '29466', '29563')
    GROUP BY
        ct.id, ct.created  
),
running_total AS (
    SELECT
        ts.id,
        ts.iso,
        ts.rate,
        ts.totalAmount,
        ts.created,  
        SUM(ts.totalAmount) OVER (ORDER BY ts.id ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS cumulativeAmount,
        ts.rate * ts.totalAmount AS totalValue
    FROM
        total_sum AS ts
)
SELECT 
    SUM(ts.totalAmount) AS total_sum  
FROM 
    running_total AS ts
WHERE 
    ts.cumulativeAmount <= '{$rowT['totalAfterDeduction']}';
";
        $rs5 = $conn->query($sql5);
        $row5 = $rs5->fetch_assoc(); 
        $first_value = $rowT['totalBeforeDeduction'] - ($row4['totalAmount'] + $row5['total_sum']);
        echo "***".$row4['totalAmount']."***";
    }
                if ($round==1){
                    $real_totalAmount = $rowNext['totalAmount'] - $first_value ;
                    echo number_format($real_totalAmount,2); 
                } else {
                    $real_totalAmount = $rowNext['totalAmount'];
                    echo number_format($real_totalAmount,2);
                }
            //echo "--".$rowT['totalBeforeDeduction'];
    
              ?></td>
          <td align="right"><?php 
                $real_totalValue = $real_totalAmount * $rateNext ;
                echo number_format($real_totalValue,2);
                //echo number_format($rowNext['totalValue'],2); 
              ?></td>
          <td align="right">-</td>
          <td align="right">-</td>
          <td align="right" class="fifo_a_<?php echo $currency;?>"><?php echo number_format($rowNext['totalAmount'],2);?></td>
          <td align="right" class="purchase_cost_a_<?php echo $currency;?>"><?php echo number_format($rowNext['totalValue'],2);?></td>
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
    } // end while
} // end if($dd == "2023-01-") ?>      
        
        
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
	AND tl.branchID = '{$_POST['branchID']}' 
	AND tl.tran_status = 2 
	AND ts.note <> 1 
	AND tl.`status` = 1 
	AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL', 'INR')
    AND ts.iso = '{$currency}'
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

$sumAmountIN = [];
$sumBuyAmountIN = [];

$total_cut_sales = [];
    
    
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
	AND tl.branchID = '{$_POST['branchID']}' 
	AND tl.tran_status = 4 
	AND ts.note <> 1 
	AND tl.`status` = 1 
	AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL', 'INR')
    AND ts.iso = '{$currency}'
    AND ct.customerID NOT IN ('1621', '29466', '29563')
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
    @$resultOut2 = $conn->query($sqlOut); 
    
$total_sum_out = []; 
while ($rowOut2 = $resultOut2->fetch_assoc()) {
    $total_sum_out[$currency] = ($total_sum_out[$currency] ?? 0) + $rowOut2['totalAmount'];
}

///////////// out //////////////   
    
$brought_forward = array();
    
    while($row = $result->fetch_assoc()) { 
     
?>        
        <tr>
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
                echo sprintf('%g', $rate);
              ?>
            </td>
          <td align="right">
              <?php 
                echo number_format($row['totalAmount'],2);
                $sumAmountIN[$currency] = ($sumAmountIN[$currency] ?? 0) + $row['totalAmount'];
              ?>
            </td>
          <td align="right">
              <?php 
                echo number_format($row['totalValue'],2); 
                @$sumBuyAmountIN[$currency] += $row['totalValue'];
              ?>
            </td>
          <td align="right">
            <?php
$sumAmountIN[$currency] = $sumAmountIN[$currency] ?? 0;
$sum_cut_sales[$currency] = $sum_cut_sales[$currency] ?? 0;
$total_cut_sales[$currency] = $sumAmountIN[$currency] + $sum_cut_sales[$currency];
        
if($total_cut_sales[$currency] > $total_sum_out[$currency]){
    $totalAmountNow = $total_cut_sales[$currency] - $total_sum_out[$currency] ;
    
    if ($totalAmountNow > $row['totalAmount'] ) {
        echo number_format($row['totalAmount'],2);
        $currentAmount = $row['totalAmount'];
        $totalValueNow = $rate * $row['totalAmount'];
    } else {
        echo number_format($totalAmountNow,2);
        $currentAmount = $totalAmountNow;
        $totalValueNow = $rate * $totalAmountNow;
    }
    @$total_sum_forword[$currency] += $currentAmount;
    
    // ส่งยอดยกมาไปต่อเดือนถัดไป
//    $brought_forward[$currency][] = array(
//        "id" => $row['id'],
//        "iso" => $currency,
//        "yearforward" => "{$dd}",
//        "rate" => $rate,
//        "amount" => $currentAmount ?? 0,
//        "total_sum" => $totalValueNow ?? 0 
//    );
} 
            ?>
            </td>
          <td align="right">
<?php
if($total_cut_sales[$currency] > $total_sum_out[$currency]){
    echo number_format($totalValueNow,2);
    @$total_sum_forword2[$currency] += $totalValueNow;
} 
?>
          </td>
          <td align="right" class="fifo_b_<?php echo $currency;?>">
<?php 
if($total_cut_sales[$currency] > $total_sum_out[$currency]){
    @$r[$currency]++;
    if($r[$currency]==1){
    @$rr[$currency] = $total_sum_out[$currency] - ($sumAmountIN[$currency]+$sum_cut_sales[$currency]-$row['totalAmount']);
    echo number_format(@$rr[$currency],2);
    }
} else {
        echo number_format($row['totalAmount'],2);
}
              
?>
          </td>
          <td align="right" class="purchase_cost_b_<?php echo $currency;?>">
<?php 
if($total_cut_sales[$currency] > $total_sum_out[$currency]){
    @$s[$currency]++;
    if($s[$currency]==1){
        @$purchase_cost[$currency] = @$rr[$currency] * $rate; 
        echo number_format(@$purchase_cost[$currency],2); 
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
          <td align="right"><strong><?php echo number_format($sumAmountIN[$currency],2);?></strong></td>
          <td align="right"><strong><?php echo number_format($sumBuyAmountIN[$currency],2);?></strong></td>
          <td align="right"><strong><?php echo number_format(@$total_sum_forword[$currency],2);?></strong></td>
          <td align="right"><strong><?php echo number_format(@$total_sum_forword2[$currency],2);?></strong></td>
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

$sumAmountOut = [];
$sumSellAmountOut = [];
    
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
                echo sprintf('%g', $rate);
              ?></td>
          <td align="right"><?php 
                echo number_format($rowOut['totalAmount'],2);
                @$sumAmountOut[$currency] += $rowOut['totalAmount'];
              ?></td>
          <td align="right" class="sell_amountout_<?php echo $currency;?>"><?php 
                echo number_format($rowOut['totalValue'],2); 
                @$sumSellAmountOut[$currency] += $rowOut['totalValue'];
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
          <td bgcolor="#A2E1FD" align="right"><strong><?php echo number_format($total_sum_out[$currency],2);?></strong></td>
          <td bgcolor="#A2E1FD" align="right"><strong><?php echo number_format($sumSellAmountOut[$currency],2);?></strong></td>
          <td bgcolor="#A2E1FD">&nbsp;</td>
          <td bgcolor="#A2E1FD" align="right">&nbsp;</td>
          <td bgcolor="#A2E1FD" align="right"><strong><div class="profit_<?php echo $currency;?>"><span>0.00</span></div></strong>
              <?php 
    //$profit = $sumSellAmountOut[$currency] - $sumBuyAmountIN[$currency] ;
    //echo number_format($profit,2);
              ?></td>
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
$(document).ready(function() {
    let currencyGroups = {}; // ผลรวมตามสกุลเงินสำหรับ fifo
    let purchaseCostGroups = {}; // ผลรวมตามสกุลเงินสำหรับ purchase_cost
    let sellAmountOutGroups = {}; // ผลรวมตามสกุลเงินสำหรับ sell_amountout
    let totalProfit = 0; // ตัวแปรเก็บกำไรรวม

    // ค้นหาค่าจากตาราง (FIFO & Purchase Cost)
    $('td[class^="fifo_a_"], td[class^="fifo_b_"], td[class^="purchase_cost_a_"], td[class^="purchase_cost_b_"]').each(function() {
        let classList = $(this).attr('class').split(' ');
        let currency = "";

        classList.forEach(function(cls) {
            if (cls.startsWith("fifo_a_") || cls.startsWith("fifo_b_")) {
                currency = cls.split('_')[2]; // fifo_a_AUD → "AUD"
            }
            if (cls.startsWith("purchase_cost_a_") || cls.startsWith("purchase_cost_b_")) {
                currency = cls.split('_')[3]; // purchase_cost_a_AUD → "AUD"
            }
        });

        let value = parseFloat($(this).text().replace(/,/g, '').trim()) || 0;

        if (currency) {
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

    // อัปเดตกำไรรวม `<div class="profit_sum">`
    let formattedTotalProfit = totalProfit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    //console.log(`กำไรรวมทั้งหมด: ${formattedTotalProfit}`);

    if ($(`.profit_sum span`).length) {
        $(`.profit_sum span`).text(formattedTotalProfit);
    } else {
        console.warn(`ไม่พบ <div class="profit_sum"> ในหน้า HTML`);
    }
});
</script>

<?php 
//echo "<pre>";
//print_r($brought_forward);
//echo "</pre>"; 
?>
