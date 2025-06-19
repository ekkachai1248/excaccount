<?php
include_once("chklogin.php");

$year = date("Y");
include_once("connectdb.php");
include_once("queryJSON.php");

// รันหน้านี้เสมอ
//echo '<script>
 //   fetch("session_restore_db.php");
//</script>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta http-equiv="Cache-Control" content="max-age=3600, must-revalidate">

  <title>ChangTai Exchange | Accounting Dashboard</title>
  <link rel="icon" href="favicon.ico" type="image/x-icon">

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
    
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="distx/css/adminlte.min.css">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="index3.php" class="nav-link">หน้าหลัก</a>
      </li>
     
    </ul>

  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.php" class="brand-link">
      <img src="distx/img/c.png" alt="ChangTai Logo" class="brand-image img-circle elevation-3"
           style="opacity: .8">
      <span class="brand-text font-weight-light"><b>ChangTai ACC.</b></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="distx/img/user.png" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info text-white">
          <?=$_SESSION['ses_name'];?>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <li class="nav-item has-treeview menu-open">
            
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="#" class="nav-link active bg-primary">
                  <i class="nav-icon fas fa-tachometer-alt"></i>
                  <p>Dashboard</p>
                </a>
              </li>
<li class="nav-header">สาขา</li>
<?php
    $sql2 = "SELECT * FROM `tb2_branch` WHERE branchID NOT IN ('5', '6') ORDER BY `tb2_branch`.`branchName` ASC"; // 5=Bangkok, 6=นครราชสีมา
    $result2 = $conn->query($sql2);
                
    $branches = [];
    while($row2 = $result2->fetch_assoc()) {
        $branches[$row2['branchID']] = $row2['branchName'];
/*        
    if ($row2['branchID'] != 7){
        $fa = "";
        $dis = "disabled";
        $navActive = "";
    } else {
        $fa = "<i class='nav-icon fas fa-fire text-danger'></i>";
        $dis = "";
        $navActive = "active bg-warning";
    }
*/    
        
if(empty($_GET['bid'])){
    $_GET['bid'] = 7 ;
    $fa = "<i class='nav-icon fas fa-fire text-danger'></i>";
    $navActive = "active bg-warning";
}
        
if($_GET['bid']== $row2['branchID']){
    $fa = "<i class='nav-icon fas fa-fire text-danger'></i>";
    $navActive = "active bg-warning";
} else {
    $fa = "";
    $navActive = "";
}


?>
              <li class="nav-item">
                <a href="<?=$_SERVER['PHP_SELF']?>?bid=<?php echo $row2['branchID'];?>" class="nav-link <?php echo $navActive;?>">
                  <i class="nav-icon fas fa-calendar-check"></i>
                  <p><?php echo $row2['branchName'];?> <?php echo $fa;?></p>
                </a>
              </li>
<?php } ?>        

            </ul>
          </li>
            
          <li class="nav-header">อื่น ๆ</li>
          <li class="nav-item">
            <a href="filter_data.php" target="_blank" class="nav-link">
              <i class="nav-icon fas fa-calendar-check"></i>
              <p class="text">บันทึกรายการคัดออก</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="logout.php" class="nav-link">
              <i class="nav-icon fas fa-sign-out-alt text-info"></i>
              <p class="text">ออกจากระบบ</p>
            </a>
          </li>

        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">ChangTai Accounting Dashboard</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="index3.php">หน้าหลัก</a></li>
              <li class="breadcrumb-item active">ChangTai Dashboard</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="d-flex justify-content-center mt-4">
      <div class="col-6">
        <div class="callout callout-info">
          <h5 class="mb-3"><i class="fas fa-map-marker-alt"></i> &nbsp; สาขา <span class="text-primary"><?php $bid = $_GET['bid']; echo $branches[$bid] ?? 'ไม่พบสาขา'; ?></span></h5>
            
<form method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>?bid=<?php echo $_GET['bid'];?>" id="yearForm">
    <div class="form-group">
        <label for="yearSelect">เลือกปี พ.ศ.</label>
        <select class="form-control" id="yearSelect" name="y" onchange="this.form.submit();">
            <?php
            $currentYear = date('Y') + 543;
            $selectedYear = isset($_POST['y']) ? $_POST['y'] : $currentYear;

            for ($year = $currentYear; $year >= 2567 ; $year--) {
                $selected = ($year == $selectedYear) ? "selected" : "";
                echo "<option value='$year' $selected>$year</option>";
            }
            ?>
        </select>
    </div>
</form>
            
        </div>
      </div>
    </div>
      
      
<div class="d-flex justify-content-center mt-4">
      <div class="col-6">
        <div class="callout callout-info">
          <h5 class="mb-3"><i class="fas fa-list-alt"></i> &nbsp; รายงานสรุปยอดคงเหลือ </h5>
            
    <div class="form-group">
        
<div class="d-flex justify-content-center align-items-center">
    <div class="col-12 col-sm-9 col-md-6">
        <a href="#" onclick="navigateToBalanceReport('<?php echo $selectedYear - 543; ?>')" style="cursor: pointer;text-decoration: none; color: #242424">
        <div class="info-box mb-3 text-center">
            <span class="info-box-icon bg-gradient-blue">
                <i class="fas fa-list"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text"><h5>พ.ศ.<?php echo $selectedYear; ?></h5></span>
            </div>
        </div>
            </a>
    </div>
</div>
        
    </div>

            
        </div>
      </div>
    </div>      

    <script>
function navigateToReportSummary(month) {
    let year = document.getElementById('yearDisplay-' + month).innerText;
    let ym = year + '-' + (month < 10 ? '0' + month : month);

    console.log("ปี-เดือน: " + ym);

    const selectedYear = document.getElementById('yearSelect').value;
    const christianYear = selectedYear - 543;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'summary_report_bs.php';
    form.target = '_blank';

    const yearInput = document.createElement('input');
    yearInput.type = 'hidden';
    yearInput.name = 'y';
    yearInput.value = christianYear;
    form.appendChild(yearInput);

    const monthInput = document.createElement('input');
    monthInput.type = 'hidden';
    monthInput.name = 'm';
    monthInput.value = month;
    form.appendChild(monthInput);

    const branchID = <?php echo isset($_GET['bid']) ? json_encode($_GET['bid']) : 'null'; ?>;
    const branchInput = document.createElement('input');
    branchInput.type = 'hidden';
    branchInput.name = 'branchID';
    branchInput.value = branchID ;
    form.appendChild(branchInput);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
        
function navigateToReport(month) {
    let year = document.getElementById('yearDisplay-' + month).innerText;
    let ym = year + '-' + (month < 10 ? '0' + month : month);

    console.log("ปี-เดือน: " + ym);

    const selectedYear = document.getElementById('yearSelect').value;
    const christianYear = selectedYear - 543;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'buying_report_a.php';
    form.target = '_blank';

    const yearInput = document.createElement('input');
    yearInput.type = 'hidden';
    yearInput.name = 'y';
    yearInput.value = christianYear;
    form.appendChild(yearInput);

    const monthInput = document.createElement('input');
    monthInput.type = 'hidden';
    monthInput.name = 'm';
    monthInput.value = month;
    form.appendChild(monthInput);

    const branchID = <?php echo isset($_GET['bid']) ? json_encode($_GET['bid']) : 'null'; ?>;
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
        const selectedYear = document.getElementById('yearSelect').value;
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
        yearInput.value = christianYear;
        form.appendChild(yearInput);

        // สร้าง input สำหรับเดือน
        //const monthInput = document.createElement('input');
        //monthInput.type = 'hidden';
        //monthInput.name = 'm';
        //monthInput.value = month;
        //form.appendChild(monthInput);

        // สร้าง input สำหรับ branchID
        const branchID = <?php echo isset($_GET['bid']) ? json_encode($_GET['bid']) : 'null'; ?>;
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

<div class="d-flex justify-content-center">
    <div class="col-9">
        
<?php
$current_session = $_SESSION['ses_id'];

// ตรวจสอบว่า session_id ปัจจุบันอยู่ในฐานข้อมูลหรือไม่
$stmt = $conn->prepare("SELECT 1 FROM session_data_fwd WHERE session_id = ?");
$stmt->bind_param("s", $current_session);
$stmt->execute();
$result = $stmt->get_result();
//var_dump($result->num_rows);
/*
if ($result->num_rows > 0) {
    // ถ้า session_id มีอยู่ในฐานข้อมูลแล้ว ให้เรียก session_restore_db.php และโหลดหน้าใหม่
    //echo "<script>fetch('session_restore_db.php').then(());</script>";
} else {
    //if (!isset($_SESSION['session_checked'])) {
        echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            function updateSession(id) {
                $.post("", { session_source: id }, function(response) {
                        fetch("session_restore_db.php").then(() => location.reload());
                });
            }
        </script>
        <p>คุณต้องการโหลดข้อมูล ยอดยกมา ล่าสุดถึงเดือนอะไร ?</p>';

        for ($i = 1; $i <= 12; $i++) {
            $button_y = 2023 + 543; // คำนวณค่าปี พ.ศ.
            printf('<button class="btn btn-outline-info btn-sm p-2" onclick="updateSession(%d)">%d-%02d</button> ', $i, $button_y, $i);
        }
    //$_SESSION['session_checked'] = true;
    //}
}
*/
        
/*
// ถ้ามีการคลิกปุ่ม ให้ทำการอัปเดตข้อมูล โดยการ insert
if (isset($_POST['session_source'])) {
    $stmt = $conn->prepare("INSERT INTO session_data_fwd (session_id, data) 
                            SELECT ?, data FROM session_data_fwd WHERE session_id = ?");
    $stmt->bind_param("ss", $current_session, $_POST['session_source']);
    $stmt->execute();
    exit;
}
*/
        
?>        
        
        <h4 class="mt-4 mb-4">รายงานการซื้อธนบัตร เลือกเดือน &nbsp; &nbsp;
            <button id="saveButton" class="btn btn-success btn-sm">
                <i class="fas fa-save"></i> บันทึก
            </button>
            <!--<button id="restoreButton" class="btn btn-success btn-sm">
                <i class="fas fa-spinner"></i> Refresh
            </button>-->
        </h4>
          
        <script>
            $('#saveButton').click(function() {
                $.post('session_into_db.php', { y: <?php echo json_encode($selectedYear); ?> }, function() {
                    location.reload();
                });
            });
            $('#restoreButton').click(function() {
                $.post('session_restore_db.php', { y: <?php echo json_encode($selectedYear); ?> }, function() {
                    location.reload();
                });
            });
        </script>
          
<!-- Info boxes -->
<?php
//session_start(); // เริ่ม session ถ้ายังไม่มี

$thaiMonths = [
    "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
    "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
];

$currentYear = date('Y') + 543; // ปีปัจจุบันใน พ.ศ.
$selectedYear = isset($_POST['y']) ? $_POST['y'] : $currentYear;
$selectedYearEN = $selectedYear - 543; // แปลง พ.ศ. เป็น ค.ศ.

$startYear = 2024; // ปีเริ่มต้น
$startMonth = 1; // เดือนเริ่มต้น

//var_dump($thaiMonths);
        
$sql3 = "SELECT * FROM session_data_fwd WHERE session_id='{$_SESSION['ses_id']}' ";
$result3 = $conn->query($sql3);
$row3 = $result3->fetch_assoc();  
$currency_data = json_decode($row3['data'], true);       

// ดึงคีย์ (ปี-เดือน) ทั้งหมดมาเรียงใหม่
$bid = $_GET['bid'];
        
//$months = array_keys($currency_data[$bid] ?? ['2025-01']);
$months = array_keys(!empty($currency_data[$bid]) && is_array($currency_data[$bid]) 
    ? $currency_data[$bid] 
    : ['2025-01']
);
sort($months);
        
// แยกปีและเดือนสุดท้ายออกมา
$lastMonth = end($months);

if ($lastMonth && strpos($lastMonth, '-') !== false) {
    list($year, $month) = explode('-', $lastMonth);
} else {
    $year = '0000';
    $month = '01';
}

// หาค่าเดือนถัดไป
$month++;
if ($month > 12) {
    $month = 1;
    $year++;
}

// จัดรูปแบบให้ได้ "YYYY-MM"
$nextPeriod = sprintf("%04d-%02d", $year, $month);

//echo $nextPeriod;


?>

<div class="row">
    <?php 
    foreach ($thaiMonths as $index => $month) {
        $counter = $index + 1;
        //$ym = $selectedYearEN . '-' . str_pad($counter, 2, '0', STR_PAD_LEFT); //var_dump($ym);
        $ym = $selectedYearEN . '-' . sprintf("%02d", $counter);

        if ($nextPeriod == $ym){ // ถ้าเดือนถัดไปเท่ากับเดือนที่แสดงให้เป็นสี warning
            $clickable = 'onclick="navigateToReport(' . $counter . ')" style="cursor: pointer;"';
            $bgColor = "bg-warning";
        } else if (strtotime($ym) > strtotime($nextPeriod)) {
            $clickable = 'onclick="navigateToReport(' . $counter . ')" style="cursor: pointer;"';
            $bgColor = "bg-secondary";
        } else {
            $clickable = 'onclick="navigateToReport(' . $counter . ')" style="cursor: pointer;"';
            $bgColor = "bg-info";
        }

    ?>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3" <?php echo $clickable; ?>>
            <span class="info-box-icon elevation-1 <?php echo $bgColor; ?>">
                <i class="fas fa-calendar-check"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text"><?php echo $month; ?></span>
                <span class="info-box-number">
                    <small>
                        <div id="yearDisplay-<?php echo $counter; ?>"><?php echo $selectedYear; ?></div>
                    </small>
                </span>
            </div>
        </div>
    </div>
    <?php 
    } ?>
</div>
        
        
      
<h4 class="mt-4 mb-4">รายงานแยกตามสกุลเงิน</h4>
<div class="row">
              <!-- /.col -->

              <div class="col-12 col-sm-12 col-md-12">
                <!-- USERS LIST -->
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">สกุลเงิน</h3>

                    <div class="card-tools">
                      <span class="badge badge-success">Buying</span>
                      
                    </div>
                  </div>
                  <!-- /.card-header -->
                  <div class="card-body p-0">
                    <ul class="users-list clearfix">

<?php
$currencies = getCurrencies();
//print_r( $currencies );
foreach ($currencies as $currency) {                     
?>
                      <li>
                          <a href="#" onclick="navigateToReportISO('<?php echo $currency['iso']; ?>')" style="cursor: pointer;">
<img src="https://changtaiexchange.com/flags/<?=strtoupper($currency['iso']);?>.png" style="height: 50px; width: 75px;box-shadow: 3px 3px 6px rgba(0, 0, 0, 0.3)" onerror="this.onerror=null; this.src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAYAAABytg0kAAAADUlEQVQIW2NkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';"><br>
                        <?=$currency['iso'];?><br>
                        <span class="text-secondary small"><?php ?></span>
                              </a>
                      </li>
<?php } ?>

                    </ul>
                    <!-- /.users-list -->
                  </div>
                  <!-- /.card-body -->
                  <div class="card-footer text-center">
                    &nbsp;
                  </div>
                  <!-- /.card-footer -->
                </div>
                <!--/.card -->
              </div>
              <!-- /.col -->
                
            </div>        
        
        
<h4 class="mt-4 mb-4">รายงานสรุปการซื้อขาย เลือกเดือน &nbsp; &nbsp;</h4>
<div class="row">
    <?php 
    foreach ($thaiMonths as $index => $month) {
        $counter = $index + 1;
        $ym = $selectedYearEN . '-' . sprintf("%02d", $counter);

            $clickable = 'onclick="navigateToReportSummary(' . $counter . ')" style="cursor: pointer;"';
            $bgColor = "bg-info";

    ?>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3" <?php echo $clickable; ?>>
            <span class="info-box-icon elevation-1 <?php echo $bgColor; ?>">
                <i class="fas fa-calendar-check"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text"><?php echo $month; ?></span>
                <span class="info-box-number">
                    <small>
                        <div id="yearDisplay-<?php echo $counter; ?>"><?php echo $selectedYear; ?></div>
                    </small>
                </span>
            </div>
        </div>
    </div>
    <?php 
    } ?>
</div>
        
        
      </div>
        

        
    </div>
      
  </div>
</section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
    
    
  <!-- Main Footer -->
  <footer class="main-footer">
    <strong>Copyright &copy; 2025 ChangTai.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 2.0.0
    </div>
<span style="font-size: 8px">    
<?php
//var_dump(session_id()); // แสดงค่า session ID
?>
</span>    
  </footer>
</div>
<!-- ./wrapper -->

<script>
function openCenteredWindow(url, title, w, h) {
    var left = (screen.width - w) / 2;
    var top = (screen.height - h) / 2;

    // เปิดหน้าต่างใหม่
    window.open(url, title, 'width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
}
</script>    
    
    
<script>
$(document).ready(() => {
    $.post("session_into_db.php", { someData: "value" }, (response) => {
        //console.log(response);  // แสดงผลลัพธ์ที่ได้รับจาก server
    });
});
</script>

    
<!-- REQUIRED SCRIPTS -->

<!-- Bootstrap -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- overlayScrollbars -->
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="distx/js/adminlte.js"></script>

<!-- OPTIONAL SCRIPTS -->
<script src="distx/js/demo.js"></script>

<!-- PAGE PLUGINS -->
<!-- jQuery Mapael -->
<script src="plugins/jquery-mousewheel/jquery.mousewheel.js"></script>
<script src="plugins/raphael/raphael.min.js"></script>
<script src="plugins/jquery-mapael/jquery.mapael.min.js"></script>
<script src="plugins/jquery-mapael/maps/usa_states.min.js"></script>
<!-- ChartJS -->
<!--<script src="plugins/chart.js/Chart.min.js"></script>-->

<!-- PAGE SCRIPTS -->
<!--<script src="distx/js/pages/dashboard2.js"></script>-->
    

</body>
</html>
