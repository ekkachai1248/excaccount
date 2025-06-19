<?php
session_start();

include_once("connectdb.php");

$currency = $_GET['iso'];
$dd = $_GET['d'];  
//var_dump($_SESSION[$currency]['2023-02']);

    $dateDD = DateTime::createFromFormat('Y-m', $dd); // สร้าง DateTime Object
    $dateDD->modify('-1 month'); // ลบ 1 เดือน
    //echo $dateDD->format('Y-m'); // ผลลัพธ์: 2023-01

if($dd=="2023-02"){

// URL ของไฟล์ JSON
$url = "http://127.0.0.1/excacc/get_brought_forward_json.php?iso={$currency}&d={$dateDD->format('Y-m')}";

// ดึงข้อมูล JSON จาก URL
$json_data = file_get_contents($url);

// ตรวจสอบว่าดึงข้อมูลสำเร็จหรือไม่
if ($json_data === false) {
    die("ไม่สามารถโหลด JSON ได้");
}

// แปลง JSON เป็น Array
$data = json_decode($json_data, true);

// ตรวจสอบ JSON ว่าแปลงสำเร็จหรือไม่
if ($data === null) {
    die("เกิดข้อผิดพลาดในการแปลง JSON");
}

// ดึงค่าจาก JSON
$totalSum = $data['totalSum'] ?? 0;
$transactions = $data['data'] ?? [];
//echo $totalSum;
//echo "รวมทั้งหมด: $totalSum <br>";
//echo "รายการทั้งหมด:\n";

/*
foreach ($transactions as $transaction) {
    echo "--------------------------------\n";
    echo "ID: " . $transaction['id'] . "\n";
    echo "วันที่: " . $transaction['created'] . "\n";
    echo "สกุลเงิน: " . $transaction['iso'] . "\n";
    echo "อัตราแลกเปลี่ยน: " . $transaction['rate'] . "\n";
    echo "ยอดรวม: " . $transaction['totalAmountNew'] . "\n";
    echo "มูลค่ารวม: " . $transaction['totalValueNew'] . "\n";
    echo "ปรับสมดุลครั้งแรก: " . ($transaction['first_adjusted_balance'] ?? "N/A") . "\n";
}

exit;
*/
} else {
    // หา total_sum ของเดือนก่อน
    $totalSum = @$_SESSION[$currency][$dateDD->format('Y-m')];
    
    
} // end if($dd=="2023-02")
    
////////////////////////
$sql2 = "SELECT SUM(totalAmount) AS grandTotal
FROM (
    SELECT SUM(ts.note * ts.amountOUT) AS totalAmount
    FROM tb2_counter_transaction AS ct
    INNER JOIN tb2_customer AS c ON ct.customerID = c.id
    INNER JOIN tb2_counter_transaction_detail AS ctd ON ct.id = ctd.cTranID
    INNER JOIN tb2_transaction_stock AS ts ON ctd.tranID = ts.tranID
    INNER JOIN tb2_transaction_log AS tl ON ctd.tranID = tl.id
    WHERE ct.created LIKE '{$dd}%' 
    AND tl.branchID = '7' 
    AND tl.tran_status = 4 
    AND ts.note <> 1 
    AND tl.status = 1 
    AND ts.iso = '{$currency}' 
    AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL', 'INR') 
    AND ct.customerID NOT IN ('1621', '29466', '29563', '6500')
    AND ct.id NOT IN ('59908')
    GROUP BY ct.id
) AS subquery;
";
$result2 = $conn->query($sql2);
$row2 = $result2->fetch_assoc();

echo $row2['grandTotal']."<br>".@$_SESSION[$currency][$dateDD->format('Y-m')]."<hr>";
////////////////////////
    
// $row2['grandTotal'] คือ sum ของ out  ,  $totalSum คือ sum ของ ยอดยกมา
$balance = $row2['grandTotal'] - $totalSum ;
$_SESSION['balance'][$currency][$dd] = $balance ;


// ถ้ายอดยกมาแล้วขายไม่หมด ให้เอามารวมกับยอดยกมาเดิม
if ($row2['grandTotal'] < $totalSum){
    
    $sql3 = "SET @balance := {$_SESSION['balance'][$currency][$dateDD->format('Y-m')]};
SET @first_negative_balance := NULL;
SET @row_count := 0;

SELECT
    id,
    created,
    iso,
    rate,
    totalAmount,
    (rate * totalAmount) AS totalValue,

    -- totalAmountNew: แถวแรกใช้ first_negative_balance, แถวอื่นใช้ totalAmount
    CASE 
        WHEN @row_count = 0 THEN @first_negative_balance  
        ELSE totalAmount
    END AS totalAmountNew,

    -- totalValueNew: คำนวณตาม totalAmountNew
    CASE 
        WHEN @row_count = 0 THEN 
            (rate * @first_negative_balance)  
        ELSE 
            (rate * totalAmount)  
    END AS totalValueNew,

    -- first_adjusted_balance: แสดงเฉพาะแถวแรก
    CASE 
        WHEN @row_count = 0 THEN totalAmount - IFNULL(@first_negative_balance, 0)  
        ELSE NULL  
    END AS first_adjusted_balance,

    @row_count := @row_count + 1
FROM (
    SELECT
        id,
        created,
        iso,
        rate,
        totalAmount,
        @balance := @balance - totalAmount AS remaining_balance,

        -- บันทึก first_negative_balance ครั้งแรกที่ balance ติดลบ
        CASE 
            WHEN @balance < 0 AND @first_negative_balance IS NULL 
            THEN @first_negative_balance := ABS(@balance) 
            ELSE @first_negative_balance 
        END AS adjusted_balance
    FROM (
        SELECT
            ct.id AS id,
            ct.created AS created,
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
            ct.created LIKE '{$dateDD->format('Y-m')}%'
            AND tl.branchID = '7'
            AND tl.tran_status = 2
            AND ts.note <> 1
            AND tl.status = 1
            AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL', 'INR')
            AND ct.id NOT IN ('59932', '60547', '59908')
            AND ts.iso = '{$currency}'
        GROUP BY ct.id
    ) AS combined_data
    ORDER BY id ASC
) AS final_result
WHERE remaining_balance < 0;
";
//var_dump($sql3);
    if ($conn->multi_query($sql3)) {
        do {
            if ($result3 = $conn->store_result()) {
                //$totalAmount2 = 0 ; // ยอดยกมา
                while ($row3 = $result3->fetch_assoc()) {
                    //print_r($row2);
                    //$totalAmount2 += $row2['totalAmountNew'];
                    echo $row3['id']." -- ".$row3['created']." -- ".$row3['iso']." -- ".$row3['rate']." -- ".$row3['totalAmount']." -- ".$row3['totalAmountNew']." -- ".$row3['first_adjusted_balance']."<br>";
                }
                //echo $totalAmount;
                //$_SESSION[$currency][$dd] = $totalAmount ;
                $result3->free();
            }
        } while ($conn->next_result());
    } else {
        echo "Error: " . $conn->error;
    }

    echo "<hr>";
}


$sql = "
SET @balance := {$balance};
SET @first_negative_balance := NULL;
SET @row_count := 0;

SELECT
    id,
    created,
    iso,
    rate,
    totalAmount,
    (rate * totalAmount) AS totalValue,

    -- totalAmountNew: แถวแรกใช้ first_negative_balance, แถวอื่นใช้ totalAmount
    CASE 
        WHEN @row_count = 0 THEN @first_negative_balance  
        ELSE totalAmount
    END AS totalAmountNew,

    -- totalValueNew: คำนวณตาม totalAmountNew
    CASE 
        WHEN @row_count = 0 THEN 
            (rate * @first_negative_balance)  
        ELSE 
            (rate * totalAmount)  
    END AS totalValueNew,

    -- first_adjusted_balance: แสดงเฉพาะแถวแรก
    CASE 
        WHEN @row_count = 0 THEN totalAmount - IFNULL(@first_negative_balance, 0)  
        ELSE NULL  
    END AS first_adjusted_balance,

    @row_count := @row_count + 1
FROM (
    SELECT
        id,
        created,
        iso,
        rate,
        totalAmount,
        @balance := @balance - totalAmount AS remaining_balance,

        -- บันทึก first_negative_balance ครั้งแรกที่ balance ติดลบ
        CASE 
            WHEN @balance < 0 AND @first_negative_balance IS NULL 
            THEN @first_negative_balance := ABS(@balance) 
            ELSE @first_negative_balance 
        END AS adjusted_balance
    FROM (
        SELECT
            ct.id AS id,
            ct.created AS created,
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
            ct.created LIKE '{$dd}%'
            AND tl.branchID = '7'
            AND tl.tran_status = 2
            AND ts.note <> 1
            AND tl.status = 1
            AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL', 'INR')
            AND ct.id NOT IN ('59932', '60547', '59908')
            AND ts.iso = '{$currency}'
        GROUP BY ct.id
    ) AS combined_data
    ORDER BY id ASC
) AS final_result
WHERE remaining_balance < 0;
"; 
//var_dump($sql);
    
    if ($conn->multi_query($sql)) {
        do {
            if ($result = $conn->store_result()) {
                $totalAmount = 0 ; // ยอดยกมา
                while ($row = $result->fetch_assoc()) {
                    //print_r($row);
                    $totalAmount += $row['totalAmountNew'];
                    echo $row['id']." -- ".$row['created']." -- ".$row['iso']." -- ".$row['rate']." -- ".$row['totalAmount']." -- ".$row['totalAmountNew']." -- ".$row['first_adjusted_balance']."<br>";
                }
                //echo $totalAmount;
                $_SESSION[$currency][$dd] = $totalAmount ;
                $result->free();
            }
        } while ($conn->next_result());
    } else {
        echo "Error: " . $conn->error;
    }
    
$conn->close();

       
?>

