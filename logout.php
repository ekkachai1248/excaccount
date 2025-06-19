<?php
    session_start();

    unset($_SESSION['ses_id']);
    unset($_SESSION['ses_name']);

        echo "<script>";
        echo "window.location='index.php';";
        echo "</script>"; 
?>