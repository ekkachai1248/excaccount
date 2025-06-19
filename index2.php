<?php
include_once("chklogin.php");

$year = date("Y");
include_once("connectdb.php");
?>

<?php
// หาจำนวน สถาบันการเงินในประเทศ

$sql5 = "
SELECT
	COUNT( * ) AS total_count 
FROM
	(
SELECT
	ct.id,
	ct.customerID,
	ct.tran_status,
	c.NAME,
	ct.created,
	c.country,
	ctd.tranID,
	ts.iso,
	ts.note,
	ts.amountOUT,
	ts.amountIN,
	ts.rateAsset,
	ts.rate,
	tl.branchID,
	tl.STATUS,
	SUM( ts.note * ts.amountIN ) AS totalAmount,
	SUM( ROUND( ts.note * ts.amountIN * ts.rate, 2 ) ) AS totalValue 
FROM
	tb2_counter_transaction AS ct
	INNER JOIN tb2_customer AS c ON ct.customerID = c.id
	INNER JOIN tb2_counter_transaction_detail AS ctd ON ct.id = ctd.cTranID
	INNER JOIN tb2_transaction_stock AS ts ON ctd.tranID = ts.tranID
	INNER JOIN tb2_transaction_log AS tl ON ctd.tranID = tl.id 
WHERE
	ct.created LIKE '{$year}-%' 
	 
	AND ( tl.tran_status = 2 OR tl.tran_status = 4 ) 
	AND ts.rate <> 1 
	AND tl.STATUS = 1 
	AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL')
GROUP BY
	ct.id 
HAVING
	ct.customerID IN (
39698,
31,
15634,
26,
24282
	) 
	) AS subquery;
	";//AND tl.branchID = 7
$result5 = $conn->query($sql5);
$row5 = $result5->fetch_assoc();
?>


<?php
// หาจำนวน MC ในประเทศ

$sql4 = "
SELECT
	COUNT( * ) AS total_count 
FROM
	(
SELECT
	ct.id,
	ct.customerID,
	ct.tran_status,
	c.NAME,
	ct.created,
	c.country,
	ctd.tranID,
	ts.iso,
	ts.note,
	ts.amountOUT,
	ts.amountIN,
	ts.rateAsset,
	ts.rate,
	tl.branchID,
	tl.STATUS,
	SUM( ts.note * ts.amountIN ) AS totalAmount,
	SUM( ROUND( ts.note * ts.amountIN * ts.rate, 2 ) ) AS totalValue 
FROM
	tb2_counter_transaction AS ct
	INNER JOIN tb2_customer AS c ON ct.customerID = c.id
	INNER JOIN tb2_counter_transaction_detail AS ctd ON ct.id = ctd.cTranID
	INNER JOIN tb2_transaction_stock AS ts ON ctd.tranID = ts.tranID
	INNER JOIN tb2_transaction_log AS tl ON ctd.tranID = tl.id 
WHERE
	ct.created LIKE '{$year}-%' 
	 
	AND ( tl.tran_status = 2 OR tl.tran_status = 4 ) 
	AND ts.rate <> 1 
	AND tl.STATUS = 1 
	AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL')
GROUP BY
	ct.id 
HAVING
	ct.customerID IN (
	1306,
	1417,
	2269,
	3176,
	7699,
	13416,
	14239,
	16334,
	20626,
	24640,
	25328,
	27796,
	40937,
	47554,
	48403,
	48404 
	) 
	) AS subquery;
	";//AND tl.branchID = 7
$result4 = $conn->query($sql4);
$row4 = $result4->fetch_assoc();
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
        <a href="index2.php" class="nav-link">หน้าหลัก</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="buying_report.php" target="_blank" class="nav-link">รายงานการซื้อ</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="selling_report.php" target="_blank" class="nav-link">รายงานการขาย</a>
      </li>
    </ul>

  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index2.php" class="brand-link">
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
                <a href="index2.php" class="nav-link active bg-primary">
                  <i class="nav-icon fas fa-tachometer-alt"></i>
                  <p>Dashboard</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="buying_report.php" target="_blank" class="nav-link">
                  <i class="nav-icon fas fa-table"></i>
                  <p>รายงานการซื้อ</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="selling_report.php" target="_blank" class="nav-link">
                  <i class="nav-icon fas fa-table"></i>
                  <p>รายงานการขาย</p>
                </a>
              </li>
            </ul>
          </li>
          <!--  
          <li class="nav-item">
            <a href="pages/widgets.html" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Widgets
                <span class="right badge badge-danger">New</span>
              </p>
            </a>
          </li>
          -->
          <li class="nav-header">เกินกำหนดต่อคนต่อเดือน</li>
          <li class="nav-item">
            <a href="buying_report_over.php" target="_blank" class="nav-link">
              <i class="nav-icon fas fa-fire text-danger"></i>
              <p class="text">การซื้อ</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="selling_report_over.php" target="_blank" class="nav-link">
              <i class="nav-icon fas fa-fire text-danger"></i>
              <p class="text">การขาย</p>
            </a>
          </li>

            
          <li class="nav-header">อื่น ๆ</li>
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
              <li class="breadcrumb-item"><a href="index2.php">หน้าหลัก</a></li>
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
        <!-- Info boxes -->
        <div class="row">
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
              <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>

<?php
// หาจำนวน คนไทย และ ชาวต่างชาติ

$sql6 = "
SELECT
	SUM( CASE WHEN subquery.country IN ( 'Thailand', 'thai', 'ไทย' ) THEN 1 ELSE 0 END ) AS total_rows_thailand,
	SUM( CASE WHEN subquery.country NOT IN ( 'Thailand', 'thai', 'ไทย' ) THEN 1 ELSE 0 END ) AS total_rows_not_thailand 
FROM
	(
SELECT
	ct.id,
	ct.customerID,
	ct.tran_status,
	c.`name`,
	ct.created,
	c.country,
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
	SUM( ROUND( ts.note * ts.amountIN * ts.rate, 2 ) ) AS totalValue 
FROM
	tb2_counter_transaction AS ct
	INNER JOIN tb2_customer AS c ON ct.customerID = c.id
	INNER JOIN tb2_counter_transaction_detail AS ctd ON ct.id = ctd.cTranID
	INNER JOIN tb2_transaction_stock AS ts ON ctd.tranID = ts.tranID
	INNER JOIN tb2_transaction_log AS tl ON ctd.tranID = tl.id 
WHERE
	ct.created LIKE '{$year}-%' 
	 
	AND ( tl.tran_status = 2 OR tl.tran_status = 4 ) 
	AND ts.rate <> 1 
	AND tl.`status` = 1 
	AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL')
GROUP BY
	ct.id,
	c.country 
	) AS subquery;
	";//AND tl.branchID = 7
$result6 = $conn->query($sql6);
$row6 = $result6->fetch_assoc();

?>

              <div class="info-box-content">
                <span class="info-box-text">คนไทย</span>
                <span class="info-box-number">
                  <?=number_format($row6['total_rows_thailand'],0);?>
                  <small>ธุรกรรม</small>
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-plane" style="transform: rotate(-45deg);"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">ชาวต่างชาติ</span>
                <span class="info-box-number">
<?php
// ชาวต่างชาติ = จำนวนชาวต่างชาติMC - (ในประเทศ + สถาบันการเงินในประเทศ)
$count_foreigner = $row6['total_rows_not_thailand'] - ($row4['total_count'] + $row5['total_count']);
echo number_format($count_foreigner,0);
?> 
		<small>ธุรกรรม</small></span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->

          <!-- fix for small devices only -->
          <div class="clearfix hidden-md-up"></div>

          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-success elevation-1"><i class="fas fa-university"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">สถาบันการเงินในประเทศ</span>
                <span class="info-box-number"><?=number_format($row5['total_count'],0);?> <small>ธุรกรรม</small></span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-flag-checkered"></i></span>

			  <div class="info-box-content">
                <span class="info-box-text">MC ในประเทศ</span>
                <span class="info-box-number"><?=number_format($row4['total_count'],0);?> <small>ธุรกรรม</small></span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->

        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title">Monthly Recap Report</h5>

                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <div class="btn-group">
                  </div>
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <div class="row">
                  <div class="col-md-8">
                    <p class="text-center">
                      <strong>Transactions for <?=$year;?></strong>
                    </p>

                    <div class="chart">
                      <!-- Sales Chart Canvas -->
                      <!--<canvas id="salesChart" height="180" style="height: 180px;"></canvas>-->
                        <?php require 'dashboard_/counter_transaction.php'; ?>
                    </div>
                    <!-- /.chart-responsive -->
                  </div>
                  <!-- /.col -->
                  <div class="col-md-4">
                    <p class="text-center">
                      <strong>Top 8 popular nationalities <?=$year;?></strong>
                    </p>

<?php
$sql2 = "
SELECT
    c.country,
    COUNT(ct.id) AS country_count
FROM
    tb2_counter_transaction AS ct
    INNER JOIN tb2_customer AS c ON ct.customerID = c.id
    INNER JOIN tb2_counter_transaction_detail AS ctd ON ct.id = ctd.cTranID
    INNER JOIN tb2_transaction_stock AS ts ON ctd.tranID = ts.tranID
    INNER JOIN tb2_transaction_log AS tl ON ctd.tranID = tl.id 
WHERE
    ct.created LIKE '{$year}-%' 
     
    AND tl.tran_status = 2 
    AND ts.rate <> 1 
    AND tl.`status` = 1 
	AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL')
GROUP BY
    c.country 
ORDER BY
    country_count DESC
LIMIT 8;
";//AND tl.branchID = 7               
$result2 = $conn->query($sql2);

$totalc = 0; 
while($row2 = $result2->fetch_assoc()) { 
    $totalc += $row2['country_count']; 
}

$result3 = $conn->query($sql2);
$icolor = -1;
while($row3 = $result3->fetch_assoc()) {
	$icolor++;
	$color_bar = array("danger", "primary", "success", "warnning", "info", "danger", "primary", "success", "warnning", "info");
	$percentc = ($row3['country_count'] / $totalc ) * 100 ;
?>                      
                      
                    <!-- /.progress-group -->
                    <div class="progress-group">
                      <?=$row3['country'];?>
                      <span class="float-right"><b><?=$row3['country_count'];?></b> (<?=number_format($percentc,2);?>%)</span>
                      <div class="progress progress-sm">
                        <div class="progress-bar bg-<?=$color_bar[$icolor];?>" style="width: <?=number_format($percentc*150/100,2);?>%"></div>
                      </div>
                    </div>
                    <!-- /.progress-group -->
<?php } ?>                      
                      
                      
                  </div>
                  <!-- /.col -->
                </div>
                <!-- /.row -->
              </div>
              <!-- ./card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->

        <!-- Main row -->
        <div class="row">
          <!-- Left col -->
          <div class="col-md-12">
            
            <!-- /.card -->
            <div class="row">
              <!-- /.col -->

              <div class="col-12 col-sm-6 col-md-6">
                <!-- USERS LIST -->
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Top 20 popular currencies <?=$year;?></h3>

                    <div class="card-tools">
                      <span class="badge badge-success">Buying</span>
                      <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                      </button>
                      <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i>
                      </button>
                    </div>
                  </div>
                  <!-- /.card-header -->
                  <div class="card-body p-0">
                    <ul class="users-list clearfix">

<?php
$sql7="
SELECT
	ts.iso,
	Count( ct.id ) AS iso_count,
	Sum( ROUND( ts.note * ts.amountIN * ts.rate, 2 ) ) AS totalValue,
	tl.tran_status 
FROM
	tb2_counter_transaction AS ct
	INNER JOIN tb2_customer AS c ON ct.customerID = c.id
	INNER JOIN tb2_counter_transaction_detail AS ctd ON ct.id = ctd.cTranID
	INNER JOIN tb2_transaction_stock AS ts ON ctd.tranID = ts.tranID
	INNER JOIN tb2_transaction_log AS tl ON ctd.tranID = tl.id 
WHERE
	ct.created LIKE '{$year}-%' 
	 
	AND tl.tran_status = 2 
	AND ts.note <> 1 
	AND tl.`status` = 1 
	AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL')
GROUP BY
	ts.iso 
ORDER BY
	totalValue DESC 
	LIMIT 20;
";//AND tl.branchID = 7
$result7 = $conn->query($sql7);
while($row7 = $result7->fetch_assoc()) { 
?>
                      <li>
<a href="#" onClick="window.open('currency_report_buy.php?iso=<?=$row7['iso'];?>', 'Summary Currency Buy Report', 'width=560,height=640,top=' + ((screen.height - 640) / 2) + ',left=' + ((screen.width - 560) / 2)); return false;"><img src="https://changtaiexchange.com/flags/<?=strtoupper($row7['iso']);?>.png" style="height: 50px; width: 75px;box-shadow: 3px 3px 6px rgba(0, 0, 0, 0.3)" onerror="this.onerror=null; this.src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAYAAABytg0kAAAADUlEQVQIW2NkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';"></a><br>
                        <?=$row7['iso'];?><br>
                        <span class="text-secondary small"><?=number_format($row7['totalValue'],0);?> ฿</span>
                      </li>
<?php } ?>

                    </ul>
                    <!-- /.users-list -->
                  </div>
                  <!-- /.card-body -->
                  <div class="card-footer text-center">
                    <a href="#" onClick="window.open('currency_allreport.php', 'View summary report', 'width=700,height=600,top=' + ((screen.height - 600) / 2) + ',left=' + ((screen.width - 700) / 2)); return false;">View summary report</a>

                  </div>
                  <!-- /.card-footer -->
                </div>
                <!--/.card -->
              </div>
              <!-- /.col -->
                
              <div class="col-12 col-sm-6 col-md-6">
                <!-- USERS LIST -->
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Top 20 popular currencies <?=$year;?></h3>

                    <div class="card-tools">
                      <span class="badge badge-primary">Selling</span>
                      <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                      </button>
                      <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i>
                      </button>
                    </div>
                  </div>
                  <!-- /.card-header -->
                  <div class="card-body p-0">
                    <ul class="users-list clearfix">

<?php
$sql8="
SELECT
	ts.iso,
	Count( ct.id ) AS iso_count,
	Sum( ROUND( ts.note * ts.amountOUT * ts.rate, 2 ) ) AS totalValue,
	tl.tran_status 
FROM
	tb2_counter_transaction AS ct
	INNER JOIN tb2_customer AS c ON ct.customerID = c.id
	INNER JOIN tb2_counter_transaction_detail AS ctd ON ct.id = ctd.cTranID
	INNER JOIN tb2_transaction_stock AS ts ON ctd.tranID = ts.tranID
	INNER JOIN tb2_transaction_log AS tl ON ctd.tranID = tl.id 
WHERE
	ct.created LIKE '{$year}-%' 
	 
	AND tl.tran_status = 4 
	AND ts.note <> 1 
	AND tl.`status` = 1 
	AND ts.iso NOT IN ('NPR', 'GIP', 'SOS', 'LTL')
GROUP BY
	ts.iso 
ORDER BY
	totalValue DESC 
	LIMIT 20;
";//AND tl.branchID = 7
$result8 = $conn->query($sql8);
while($row8 = $result8->fetch_assoc()) { 
?>
                      <li>
    <a href="#" onClick="window.open('currency_report_sell.php?iso=<?=$row8['iso'];?>', 'Summary Currency Buy Report', 'width=560,height=640,top=' + ((screen.height - 640) / 2) + ',left=' + ((screen.width - 560) / 2)); return false;"><img src="https://changtaiexchange.com/flags/<?=strtoupper($row8['iso']);?>.png" style="height: 50px; width: 75px;box-shadow: 3px 3px 6px rgba(0, 0, 0, 0.3)" onerror="this.onerror=null; this.src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAYAAABytg0kAAAADUlEQVQIW2NkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';"></a><br>
                        <?=$row8['iso'];?><br>
                        <span class="text-secondary small"><?=number_format($row8['totalValue'],0);?> ฿</span>
                      </li>
<?php } ?>

                    </ul>
                    <!-- /.users-list -->
                  </div>
                  <!-- /.card-body -->
                  <div class="card-footer text-center">
                    <a href="#" onClick="window.open('currency_allreport.php', 'View summary report', 'width=700,height=600,top=' + ((screen.height - 600) / 2) + ',left=' + ((screen.width - 700) / 2)); return false;">View summary report</a>
                  </div>
                  <!-- /.card-footer -->
                </div>
                <!--/.card -->
              </div>                
                
            </div>
            <!-- /.row -->
            
            <!-- /.card -->
          </div>
          <!-- /.col -->
          
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div><!--/. container-fluid -->
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
    <strong>Copyright &copy; 2024 ChangTai.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 1.0.0
    </div>
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
    
<!-- REQUIRED SCRIPTS -->
<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
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
<script src="plugins/chart.js/Chart.min.js"></script>

<!-- PAGE SCRIPTS -->
<script src="distx/js/pages/dashboard2.js"></script>
</body>
</html>
