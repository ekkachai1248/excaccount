<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
</head>

<body>
<?php
include_once("connectdb.php");

$currency = $_GET['iso'];
$dd = $_GET['d'];  
    
//$currency = "CAD";
//$dd = "2023-01";    
    
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
    GROUP BY ct.id
) AS subquery;
";
$result2 = $conn->query($sql2);
$row2 = $result2->fetch_assoc();
//echo $row2['grandTotal'];
////////////////////////
    

$balance = $row2['grandTotal'] ;


//if ($dd=='2023-01-'){
    
$sql = "
SET @balance := {$balance};  
SET @cumulative_sum := 0;  
SET @first_negative_balance := NULL;  
SET @first_adjusted_balance := NULL; 
SET @row_count := 0; 

SELECT 
    id, 
    created,  
    iso, 
    rate, 
    totalAmount,
    (rate * totalAmount) AS totalValue,  
    (totalAmount - IFNULL(adjusted_balance, 0)) AS totalAmountNew,  
    (rate * (totalAmount - IFNULL(adjusted_balance, 0))) AS totalValueNew,  
    CASE 
        WHEN @row_count = 0 THEN @first_adjusted_balance  
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
        @cumulative_sum := @cumulative_sum + totalAmount AS running_total, 
        @balance := @balance - totalAmount AS remaining_balance,
        CASE 
            WHEN @first_negative_balance IS NULL AND @balance < 0 
            THEN @first_negative_balance := @balance + totalAmount  
            ELSE NULL  
        END AS adjusted_balance,
        CASE 
            WHEN @first_adjusted_balance IS NULL AND @first_negative_balance IS NOT NULL 
            THEN @first_adjusted_balance := @first_negative_balance  
            ELSE NULL  
        END AS first_adjusted_balance
    FROM (
        SELECT 
            brought_forward.id AS id,  
            NULL AS created,  
            iso, 
            rate, 
            totalAmount 
        FROM 
            brought_forward 
        WHERE 
            iso = '{$currency}' 

        UNION ALL 

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
            AND ts.iso NOT IN ( 'NPR', 'GIP', 'SOS', 'LTL', 'INR' ) 
            AND ts.iso = '{$currency}' 
        GROUP BY 
            ct.id 
    ) AS combined_data
    ORDER BY id ASC, iso ASC, totalAmount DESC
) AS final_result
WHERE remaining_balance < 0;  

"; 
//var_dump($sql);
    
//} else {    // ถ้าไม่ใช่ 2023-01-
    
    echo $balance."<hr>";
    $dateDD = DateTime::createFromFormat('Y-m', $dd); // สร้าง DateTime Object
    $dateDD->modify('-1 month'); // ลบ 1 เดือน
    //echo $dateDD->format('Y-m-'); // ผลลัพธ์: 2023-01-
    
//}
    
    if ($conn->multi_query($sql)) {
        do {
            if ($result = $conn->store_result()) {
                while ($row = $result->fetch_assoc()) {
                    //print_r($row);
                    @$sum += $row['totalAmountNew'];
                    echo $row['id']." -- ".$row['created']." -- ".$row['iso']." -- ".$row['rate']." -- ".$row['totalAmountNew']." -- ".$row['first_adjusted_balance']."<br>";
                }
                echo $sum;
                $result->free();
            }
        } while ($conn->next_result());
    } else {
        echo "Error: " . $conn->error;
    }
    
$conn->close();

    
/*
$conn->multi_query($sql);    
$result = $conn->store_result();
while ($row = $result->fetch_assoc()) {
    echo $row['created']." -- ".$row['iso']." -- ".$row['rate']." -- ".$row['totalAmountNew']." -- ".$row['first_adjusted_balance']."<br>";
}
*/    
?>

    
</body>
</html>