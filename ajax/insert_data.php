<?php  require_once('../includes/db.php');  ?>
<?php  require_once('../includes/session.php');  ?>
<?php  require_once('../includes/functions.php');  ?>


<?php

if (isset($_POST["user_month"]) && isset($_POST['user_id']) && isset($_POST['meter']) && isset($_POST['remeter']) && isset($_POST['meter_picture'])) {

    $Alert_message = "";
    $Transaction = '0605';
    $Notistatus= 'unseen'; 
    $Daysremain= '360';
    $status = "Unpaid";
    $directory = 'meterphoto/';

    // for fetching inputs from ajax

    $Name = mysqli_real_escape_string($conn,$_POST['user_id']);
    $Month = mysqli_real_escape_string($conn,$_POST['user_month']);
    $Meter_reading = mysqli_real_escape_string($conn, $_POST['meter']);
    $Re_reading = mysqli_real_escape_string($conn, $_POST['remeter']);
    $Trans_pin = mysqli_real_escape_string($conn, $_POST['transa_pin']);
    $Mains = mysqli_real_escape_string($conn, $_POST['ourmeter']);

    // Calculation of bill amount 

    $consumed = $Re_reading - $user_prevreding - $Mains;
    $Amount = $consumed * $user_rate;

    // fetch details to compare with previous response

    $reading_search = "SELECT * FROM users WHERE user_name = '$Name'";
    $previous_query = mysqli_query($conn, $reading_search);
    $reading_fetch = mysqli_fetch_assoc($previous_query);
    $user_prevreding = $reading_fetch['user_lreading'];
    $user_rate = $reading_fetch['user_rate'];

    // for time zones

    date_default_timezone_set("Asia/Kathmandu");
    $CurrentTime= time();
    $DateTime = strftime("%Y-%m-%d %H:%M:%S", $CurrentTime);

    // To check if input is empty

    if (empty($Meter_reading) || empty($Re_reading) || empty($Trans_pin)|| empty($Month) || empty($Name)){

                $Alert_message ="<div class=\"alert alert-danger\">";
                $Alert_message .="Please enter all the field!!";
                $Alert_message .="</div>";
                echo $Alert_message;
    }else{
        // for checking readings match with previous or not

        if( $Meter_reading == $user_prevreding){
                $Alert_message ="<div class=\"alert alert-danger\">";
                $Alert_message .="Readings match with previous reading!!";
                $Alert_message .="</div>";
                echo $Alert_message;
        }else{
                // for checking transaction pin
            if ($Transaction == $Trans_pin) {

                // For checking the picture and inserting data
                if (isset($_FILES['meter_picture'])) {

                $filename = $_FILES['meter_picture']['name'];
                $filetmp = $_FILES['meter_picture']['tmp_name'];
                $filetype = $_FILES['meter_picture']['type'];
                $file_extension = pathinfo($filename, PATHINFO_EXTENSION);

                $valid_extensions = array("jpg", "png", "jpeg");

                // for checking file type
                if(in_array($file_extension, $valid_extensions){
                
                // For creating new name
                $new_name = rand() .'.'.$file_extension;

                $destination_directory = $directory . $new_name;

                // For compressing and uploading

                if(compressImage($filetmp,$destination_directory,60)){

                $stmt = $conn->prepare("insert into readings(read_user, read_value, read_month, read_file ,read_status, read_date, read_amount, read_cons) values(?,?, ?, ?,?,?,?,?)");
                $stmt->bind_param("ssssssss", $Name, $Re_reading, $Month, $directory ,$status, $DateTime, $Amount,$consumed);
                $Execute = $stmt->execute();

                // to check wheter data are uploaded or not

                if($Execute){

                // For updating current reading in users table
                $updatequery = "UPDATE users SET user_lreading='$Re_reading', user_ledit ='$DateTime' WHERE user_name='$Name'";
                $query = mysqli_query($conn, $updatequery);

                // For inserting notification
                $Notimessage= $Name." Bill for ".$Month." is Rs ". $Amount .".";
                $Notistmt = $conn->prepare("insert into notification(alert_name, alert_message, alert_status, alert_userstatus , alert_date, days_left) values(?,?, ?, ?,?,?)");
                $Notistmt->bind_param("ssssss", $Name, $Notimessage, $Notistatus, $Notistatus , $DateTime,$Daysremain);
                $NotiExecute = $Notistmt->execute();
                
                $Notistmt->close();
                $stmt->close();
                $conn->close();

                $Alert_message ="<div class=\"alert alert-success\">";
                $Alert_message .="Bill Added Successfully. Bill amount of ".$Name." for ".$Month." is Rs ". $Amount .".";
                $Alert_message .="</div>";
                echo $Alert_message;

                }else{
                
                
                $Alert_message ="<div class=\"alert alert-danger\">";
                $Alert_message .="Reading details not added!!";
                $Alert_message .="</div>";
                echo $Alert_message;
                $Notistmt->close();
                $stmt->close();
                $conn->close();
                }

                }else{
                $Alert_message ="<div class=\"alert alert-danger\">";
                $Alert_message .="Error in compress and upload!!";
                $Alert_message .="</div>";
                echo $Alert_message;
                }

                }else{
                $Alert_message ="<div class=\"alert alert-danger\">";
                $Alert_message .="Sorry only images are alowed!";
                $Alert_message .="</div>";
                echo $Alert_message;
                }


                }else{
                $Alert_message ="<div class=\"alert alert-danger\">";
                $Alert_message .="Image not received by Ajax";
                $Alert_message .="</div>";
                echo $Alert_message;
                }

            }else{
                $Alert_message ="<div class=\"alert alert-danger\">";
                $Alert_message .="Wrong Transaction pin!!";
                $Alert_message .="</div>";
                echo $Alert_message;
            }
        }


    }

}else{
    // Error for missing inputs

                $Alert_message ="<div class=\"alert alert-danger\">";
                $Alert_message .="Some inputs are missing!!";
                $Alert_message .="</div>";
                echo $Alert_message;
}

echo "data has been received";
?>