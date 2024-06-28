<?php  require_once('../includes/db.php');  ?>
<?php  require_once('../includes/session.php');  ?>
<?php  require_once('../includes/functions.php');  ?>


<?php

if (isset($_POST['search_name']) && isset($_POST['search_month'])) {
    $Name= mysqli_real_escape_string($conn, $_POST['search_name']);
    $Month= mysqli_real_escape_string($conn, $_POST['search_month']);
    $message="";


    $user_search = "SELECT * FROM readings WHERE read_user ='$Name' AND read_month ='$Month'";
    $User_query = mysqli_query($conn, $user_search);
    $rows = mysqli_num_rows($User_query);
    if ($rows != 0) {
        $message ="<div class=\"alert alert-danger\">";
        $message .=$Name." reading for ".$Month." has aready been added. Choose next!";
        $message .="</div>";
        echo $message;
    } else{
        echo "";
    }
}
?>