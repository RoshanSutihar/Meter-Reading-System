<?php  
require_once('../includes/db.php');
require_once('../includes/session.php');
require_once('../includes/functions.php');

if (isset($_POST['username']) && isset($_POST['new_reading'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $newReading = (int)$_POST['new_reading'];
    $message = "";

    // Get the last reading from users table
    $query = "SELECT user_lreading FROM users WHERE user_name = '$username'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $lastReading = (int)$row['user_lreading'];
        
        if ($newReading <= $lastReading) {
            $message = "<div class=\"alert alert-danger\">";
            $message .= "New reading ($newReading) must be greater than last recorded reading ($lastReading)";
            $message .= "</div>";
            echo $message;
        } else {
            echo "";
        }
    } else {
        // If no last reading found (new user), allow any positive reading
        if ($newReading <= 0) {
            $message = "<div class=\"alert alert-danger\">";
            $message .= "Reading must be a positive number";
            $message .= "</div>";
            echo $message;
        } else {
            echo "";
        }
    }
}
?>