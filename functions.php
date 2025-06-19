<?php
include_once("connectdb.php");

// function หาผลรวมของ OUT
function get_sum_out($branchID, $iso, $ym){
    global $conn ;
    
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
    
    $sql = "SELECT
    ts.iso,
    SUM(ts.note * ts.amountOUT) AS totalAmount
FROM
    tb2_counter_transaction AS ct
    INNER JOIN tb2_counter_transaction_detail AS ctd ON ct.id = ctd.cTranID
    INNER JOIN tb2_transaction_stock AS ts ON ctd.tranID = ts.tranID
    INNER JOIN tb2_transaction_log AS tl ON ctd.tranID = tl.id
WHERE
    ct.created LIKE '{$ym}%'
    AND tl.branchID = '{$branchID}'
    AND ts.iso = '{$iso}'
    AND tl.tran_status = 4
    AND ts.note <> 1
    AND tl.status = 1
    AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL', 'INR')
    AND ct.id NOT IN ({$dataOut})
GROUP BY ts.iso
ORDER BY ts.iso ASC;
"; // AND ct.id NOT IN ('1621', '29466', '29563', '59932', '55256', '54312', '55793', '55835', '59908')
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['totalAmount'] ?? 0; // คืนค่า totalAmount หรือ 0 ถ้าไม่มีข้อมูล
}


function convertDateTH2EN($date) {
    return DateTime::createFromFormat('j/n/y', $date)->format('Y-m-d');
}
//echo convertDateTH2EN('28/1/66'); // แสดงผล: 2023-01-28

?>