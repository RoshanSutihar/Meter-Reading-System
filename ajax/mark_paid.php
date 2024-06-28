<?php  require_once('../includes/db.php');  ?>
<?php  require_once('../includes/session.php');  ?>
<?php  require_once('../includes/functions.php');  ?>

<?php

    

if (isset($_POST['change_id'])) {
    $Id= mysqli_real_escape_string($conn, $_POST['change_id']);
    $message="";
    
    $user_search = "SELECT * FROM readings where read_id='$Id'";
    $User_query = mysqli_query($conn, $user_search);
    $User_fetch = mysqli_fetch_assoc($User_query);
    $Name = $User_fetch['read_user'];
    $Month = $User_fetch['read_month'];
    if($User_fetch['read_status']=='Unpaid'){

    $updatequery = "UPDATE readings SET read_status='Paid' WHERE read_id='$Id'";
    $query = mysqli_query($conn, $updatequery);

    if($query){
        $message ="<div class=\"alert alert-success\">";
        $message .= $Name. " bill for ". $Month." has been marked as paid!!";
        $message .="</div>";
        echo $message;
    } else{
        $message ="<div class=\"alert alert-danger\">";
        $message .="Something went wrong!!";
        $message .="</div>";
        echo $message;
    }
} else{
    $message ="<div class=\"alert alert-danger\">";
        $message .="Reading had already been marked as paid before only!!";
        $message .="</div>";
        echo $message;
}
}
?>