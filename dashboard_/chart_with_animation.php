<?php
// ตัวอย่างข้อมูล JSON ที่ได้จากการดึงข้อมูลจากฐานข้อมูล
$json_data = '[{"month_year":"2025-01","total_rows_status_2":10,"total_rows_status_4":20},{"month_year":"2024-02","total_rows_status_2":15,"total_rows_status_4":25},{"month_year":"2024-03","total_rows_status_2":20,"total_rows_status_4":30}]';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chart.js Example</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script> <!-- เพิ่ม plugin นี้ -->
</head>
<body>

<canvas id="myChart" width="400" height="200"></canvas>

<script>
    // รับข้อมูล JSON จาก PHP
    const jsonData = <?php echo $json_data; ?>;

    // เตรียมข้อมูลสำหรับกราฟ
    const labels = jsonData.map(data => data.month_year);
    const dataStatus2 = jsonData.map(data => data.total_rows_status_2);
    const dataStatus4 = jsonData.map(data => data.total_rows_status_4);

    const ctx = document.getElementById('myChart').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Rows Status 2',
                data: dataStatus2,
                fill: true, // เปิด fill color
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)', // สีพื้นหลังที่ถูก fill
                tension: 0.1
            },
            {
                label: 'Total Rows Status 4',
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
                    display: true, // แสดงค่า data label
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

</body>
</html>
