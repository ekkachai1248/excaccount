<?php
//include_once("chklogin.php");
include_once("connectdb.php");
include_once("queryJSON.php");

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
        <h2 class="mb-4"><i class="fas fa-bookmark"></i> รายงาน<span class="text-primary">การซื้อ</span>ธนบัตรต่างประเทศ</h2>
        <form action="" method="post">

<div class="form-row">

    <div class="form-group col-md-12">
        <label for="country">ชื่อบุคคลรับอนุญาต:</label>
        <select class="form-control" name="branch">
        <?php
            $sql2 = "SELECT * FROM `tb2_branch` WHERE `branchID`='7' ORDER BY `tb2_branch`.`branchName` ASC";
            //$sql2 = "SELECT * FROM `view_tb2_branch` WHERE `view_tb2_branch`.`branchID`='7'  ORDER BY `view_tb2_branch`.`branchID` ASC"; // 7 = หนองคาย
            $result2 = $conn->query($sql2);
            while($row2 = $result2->fetch_assoc()) {
                //if ($row2['branchName']=="หนองคาย"){
                    //$brName = "บริษัท ช้างไท เอ็กเชนจ์ (หนองคาย) จำกัด";
                //}
                if ($row2['branchID'] == $_POST['branch']){
                    $sld = "selected";
                } else {
                    $sld = "";
                }
                echo "<option value='{$row2['branchID']}' {$sld}>{$row2['branchName']} ({$row2['branchCompany']})</option>";
            }
        ?>
        </select>
    </div>
</div>      
            
<div class="form-row">            
    <div class="form-group col-md-6">
        <label for="country">ประจำงวด (เดือน):</label>
        <select class="form-control" name="Cmonth">
            <option value="">--- เลือก ---</option>
        <?php
            for($i=1; $i<=12; $i++){
                $iv = sprintf("%02d", $i);  // เติม 0 หน้าเดือน 1 หลัก
                //if($i == trim($_POST['Cmonth'])){
                if($i == 1){
                    $selectedM = "selected"; 
                } else {
                    $selectedM = "";
                }
                
                echo "<option value='{$iv}' {$selectedM}>{$i}</option>";
            }
        ?>
        </select>
    </div>

    <div class="form-group col-md-6">
        <label for="country">ประจำงวด (ปี):</label>
        <select class="form-control" name="Cyear" required>
            <!--<option value="">-- เลือก --</option>-->
        <?php 
            for($j=date('Y'); $j>=2014; $j--){
                //if ($j == $_POST['Cyear']){
                if ($j == 2023){
                    $selectedY = "selected"; 
                } else {
                    $selectedY = "";
                }
                
                echo "<option value='{$j}' {$selectedY}>{$j}</option>";
            }
        ?>
        </select>
    </div>
</div>          

        <button type="submit" class="btn btn-primary" name="Submit"><i class="fas fa-search"></i> ค้นหา (Search)</button>     
        <button type="button" class="btn btn-info" name="Cancel" onClick="window.location='<?=$_SERVER['PHP_SELF'];?>';"><i class="fas fa-undo-alt"></i> ยกเลิก (Cancel)</button>
        <button type="button" class="btn btn-warning" name="Export" id="exportBtn"><i class="fas fa-save"></i> ส่งออก (Export)</button>
        </form>
    </div>

<div class="container">
<?php
if(!isset($_POST['Submit'])){
   exit;
}
    
if(empty($_POST['date1']) and empty($_POST['Cmonth'])){
   exit;
}

if(!empty($_POST['date1'])){
    $dd = $_POST['date1'];
} else {
    if(!empty($_POST['Cmonth'])){
        $dd = "{$_POST['Cyear']}-{$_POST['Cmonth']}-";
    } else {
        $dd = $_POST['date1'];
    }
}
        
$sql3 = "SELECT * FROM `tb2_branch` WHERE `branchID` = '{$_POST['branch']}' ";
$result3 = $conn->query($sql3);
$row3 = $result3->fetch_assoc();

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
	AND tl.branchID = '{$_POST['branch']}' 
	AND tl.tran_status = 2 
	AND ts.note <> 1 
	AND tl.`status` = 1 
	AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL')
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
        ct.created LIKE '2023-01-%' 
        AND tl.branchID = '7' 
        AND tl.tran_status = 2 
        AND ts.note <> 1 
        AND tl.`status` = 1 
        AND ts.iso NOT IN ( 'NPR', 'GIP', 'SOS', 'LTL' ) 
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
          <th colspan="17" bgcolor="" class="text-center h5" style="height: 84px; font-weight: bold;">รายงานการซื้อธนบัตรและเช็คเดินทางต่างประเทศของบุคคลรับอนุญาต ( หนองคาย ) ประจำเดือน มกราคม <?php echo $_POST['Cyear']+543;?></th>
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
$isoArray = array("AED", "AUD", "BND", "CAD");  // Test 4 iso
foreach ($isoArray as $currency){
    
    
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
	AND tl.branchID = '{$_POST['branch']}' 
	AND tl.tran_status = 2 
	AND ts.note <> 1 
	AND tl.`status` = 1 
	AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL')
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

$sumAmountIN = 0;
$sumBuyAmountIN = 0;
    
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
                $sumAmountIN += $row['totalAmount'];
              ?>
            </td>
          <td align="right">
              <?php 
                echo number_format($row['totalValue'],2); 
                $sumBuyAmountIN += $row['totalValue'];
              ?>
            </td>
          <td width="104">&nbsp;</td>
          <td>&nbsp;</td>
          <td align="right"><?php echo number_format($row['totalAmount'],2);?></td>
          <td align="right"><?php echo number_format($row['totalValue'],2);?></td>
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
          <td align="right"><strong><?php echo number_format($sumAmountIN,2);?></strong></td>
          <td align="right"><strong><?php echo number_format($sumBuyAmountIN,2);?></strong></td>
          <td align="right"><strong>0.00</strong></td>
          <td align="right"><strong>0.00</strong></td>
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
	AND tl.branchID = '{$_POST['branch']}' 
	AND tl.tran_status = 4 
	AND ts.note <> 1 
	AND tl.`status` = 1 
	AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL')
    AND ts.iso = '{$currency}'
GROUP BY
        ct.id
ORDER BY
	ts.iso ASC,
	ct.created ASC
";  // limit 300  
    // tb2_transaction_log.branchID = '7' = หนองคาย
    // tb2_transaction_log.tran_status = '2' : 2=buy 4=sell    
    $resultOut = $conn->query($sqlOut);

$sumAmountOut = 0;
$sumSellAmountOut = 0;
    
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
                $sumAmountOut += $rowOut['totalAmount'];
              ?></td>
          <td align="right"><?php 
                echo number_format($rowOut['totalValue'],2); 
                $sumSellAmountOut += $rowOut['totalValue'];
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
          <td bgcolor="#A2E1FD" align="right"><strong><?php echo number_format($sumAmountIN,2);?></strong></td>
          <td bgcolor="#A2E1FD" align="right"><strong><?php echo number_format($sumBuyAmountIN,2);?></strong></td>
          <td bgcolor="#A2E1FD" align="center">&nbsp;</td>
          <td bgcolor="#A2E1FD">&nbsp;</td>
          <td bgcolor="#A2E1FD" align="right">&nbsp;</td>
          <td bgcolor="#A2E1FD" align="right"><strong><?php echo number_format($sumAmountOut,2);?></strong></td>
          <td bgcolor="#A2E1FD" align="right"><strong><?php echo number_format($sumSellAmountOut,2);?></strong></td>
          <td bgcolor="#A2E1FD">&nbsp;</td>
          <td bgcolor="#A2E1FD" align="right">&nbsp;</td>
          <td bgcolor="#A2E1FD" align="right"><strong>
              <?php 
    $profit = $sumSellAmountOut - $sumBuyAmountIN ;
    echo number_format($profit,2);
              ?></td>
        </tr>
<?php } // end foreach iso ?>
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
            for (var R = 2; R <= 5; ++R) {
                for (var C = 6; C <= 14; ++C) {
                    worksheet.getRow(R).getCell(C).border = {
                        top: null,
                        left: null,
                        bottom: null,
                        right: null
                    };
                }
            }
            
// ผสานเซลล์ 
worksheet.mergeCells('A1:R1');
//worksheet.mergeCells('A6:R6');
//worksheet.mergeCells('F2:R2');
//worksheet.mergeCells('F3:R3');
//worksheet.mergeCells('F4:R4');
//worksheet.mergeCells('F5:R5');
            
            workbook.xlsx.writeBuffer().then(function(buffer) {
                saveAs(new Blob([buffer], {type: "application/octet-stream"}), 'buying_report.xlsx');
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

