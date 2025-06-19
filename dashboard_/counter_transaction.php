<?php
include_once("./connectdb.php");
?>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

<?php
$year = date("Y") ;
$sql = "
SELECT
    DATE_FORMAT(ct.created, '%Y-%m') AS month_year,
    SUM(CASE WHEN tl.tran_status = 2 THEN 1 ELSE 0 END) AS total_buy_2,
    SUM(CASE WHEN tl.tran_status = 4 THEN 1 ELSE 0 END) AS total_sell_4
FROM
    tb2_counter_transaction AS ct
INNER JOIN 
    tb2_counter_transaction_detail AS ctd ON ct.id = ctd.cTranID
INNER JOIN 
    tb2_transaction_stock AS ts ON ctd.tranID = ts.tranID
INNER JOIN 
    tb2_transaction_log AS tl ON ctd.tranID = tl.id
WHERE
    YEAR(ct.created) = {$year}
    
    AND tl.tran_status IN (2, 4)
    AND ts.rate <> 1
	AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL')
GROUP BY
    month_year
ORDER BY
    month_year ASC;

";//AND tl.branchID = 7

    
// ดึงผลลัพธ์จากฐานข้อมูล
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // สร้างอาร์เรย์เพื่อเก็บผลลัพธ์
    $data = array();

    // ดึงข้อมูลจากผลลัพธ์และใส่ในอาร์เรย์
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    // แปลงอาร์เรย์เป็น JSON
    $json_data = json_encode($data, JSON_UNESCAPED_UNICODE);

    // แสดง JSON
    // echo $json_data;
} else {
    echo "[]"; // แสดง JSON ว่างถ้าไม่มีข้อมูล
}

?>   
    
<canvas id="Chart1" width="400" height="300"></canvas>
    
<script>
    // รับข้อมูล JSON จาก PHP
    const jsonData = <?php echo $json_data; ?>;

    // เตรียมข้อมูลสำหรับกราฟ
    const labels = jsonData.map(data => data.month_year);
    const dataStatus2 = jsonData.map(data => data.total_buy_2);
    const dataStatus4 = jsonData.map(data => data.total_sell_4);

    const ctx = document.getElementById('Chart1').getContext('2d');
    const Chart1 = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Rows Buy',
                data: dataStatus2,
                fill: true, // เปิด fill color
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)', // สีพื้นหลังที่ถูก fill
                tension: 0.1
            },
            {
                label: 'Total Rows Sell',
                data: dataStatus4,
                fill: true, // เปิด fill color
                borderColor: 'rgba(153, 102, 255, 1)',
                backgroundColor: 'rgba(153, 102, 255, 0.2)', // สีพื้นหลังที่ถูก fill
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 1000, // ระยะเวลาการเคลื่อนไหว (ในหน่วยมิลลิวินาที)
                easing: 'linear', // รูปแบบการเคลื่อนไหว
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                datalabels: {
                    display: false, // แสดงค่า data label
                    align: 'top', // ตำแหน่งของ label
                    backgroundColor: '#ccc',
                    borderRadius: 3,
                    font: {
                        size: 12,
                    },
                    formatter: (value) => value, // รูปแบบการแสดงผล
                }
            }
        },
        plugins: [ChartDataLabels] // เปิดใช้งาน plugin data labels
    });
</script>

    
