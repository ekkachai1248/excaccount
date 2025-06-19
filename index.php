<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>ChangTai Exchange 2025</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="favicon.ico" type="image/x-icon">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="distx/css/adminlte.min.css">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
    <b>ChangTai Exchange<br><div class="text-primary">Accounting system</div></b>
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body mt-2 rounded">
      <p class="login-box-msg">Sign in</p>

      <form action="<?=$_SERVER['PHP_SELF'];?>" method="post">
        <div class="input-group mb-3">
          <input type="text" class="form-control" placeholder="Username" name="user_login" autofocus required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user-alt"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="Password" name="pwd_login" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8">
            <div class="icheck-primary">
            </div>
          </div>
          <!-- /.col -->
          <div class="col-4">
            <!--<button type="button" class="btn btn-primary btn-block" onClick="window.location='index2.php';">Sign In</button>-->
            <button type="submit" name="Submit" class="btn btn-primary btn-block">Sign In</button>
          </div>
          <!-- /.col -->
        </div>
      </form>

      <div class="social-auth-links text-center mb-3">
      </div>
      <!-- /.social-auth-links -->

    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="distx/js/adminlte.min.js"></script>

    
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include_once("connectdb.php");
    
$users = [
    ["usr" => "reportadmin", "pwd" => "123789", "name" => "แอดมิน"],
    ["usr" => "reportr", "pwd" => "123789", "name" => "รสจรินทร์"],
    ["usr" => "reporty", "pwd" => "123789", "name" => "ยงยุทธ"]
];
    
$username = $_POST['user_login'] ?? '';
$password = $_POST['pwd_login'] ?? '';

foreach ($users as $user) {
    if ($user['usr'] === $username && $user['pwd'] === $password) {
        $_SESSION['ses_id'] = $user['usr'];
        $_SESSION['ses_name'] = $user['name'];
        header("Location: index3.php");
        exit();
    }
}

echo "<p style='color:red;'>Username or Password is incorrect</p>";
    
}
?>
    
</body>
</html>
