<?php  require_once('../includes/db.php');  ?>
<?php  require_once('../includes/session.php');  ?>
<?php  require_once('../includes/functions.php');  ?>

<?php
$money_left = "0";

$query = "SELECT SUM(read_amount) as 'leftamount' from readings WHERE read_status='Unpaid' AND read_user='Shater2'"; 
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);
$money_left = $data['leftamount'];
if ($money_left > 0) {
    echo $money_left;
} else {
    echo '0';
}

?>

