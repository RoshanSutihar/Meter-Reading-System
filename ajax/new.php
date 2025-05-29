<?php  require_once('../includes/db.php');  ?>
<?php  require_once('../includes/session.php');  ?>
<?php  require_once('../includes/functions.php');  ?>


<?php

    $Alert_message = "";
    $Transaction = '0605';
    $Name = $_POST['user_id'];
    $Month = $_POST['user_month'];
    $Notistatus= 'unseen'; 
    $Daysremain= '360';
    $status = "Unpaid";

    $reading_search = "SELECT * FROM users WHERE user_name = '$Name'";
    $previous_query = mysqli_query($conn, $reading_search);
    $reading_fetch = mysqli_fetch_assoc($previous_query);
    $user_prevreding = $reading_fetch['user_lreading'];
    $user_rate = $reading_fetch['user_rate'];

    $Meter_reading = mysqli_real_escape_string($conn, $_POST['meter']);
    $Re_reading = mysqli_real_escape_string($conn, $_POST['remeter']);
    $Trans_pin = mysqli_real_escape_string($conn, $_POST['transa_pin']);
    $Mains = mysqli_real_escape_string($conn, $_POST['ourmeter']);
    $consumed = $Re_reading-$user_prevreding-$Mains;
    $Amount = $consumed * $user_rate;

    // for image end
    date_default_timezone_set("Asia/Kathmandu");
    $CurrentTime= time();
    $DateTime = strftime("%Y-%m-%d %H:%M:%S", $CurrentTime);

    if (empty($Meter_reading) || empty($Re_reading) || empty($Trans_pin)||empty($Month)){
        $Alert_message ="<div class=\"alert alert-danger\">";
        $Alert_message .="Please insert all the required field!!";
        $Alert_message .="</div>";
        echo $Alert_message;
    }
    
    }else{

    if ($Meter_reading !== $Re_reading) {
        $Alert_message ="<div class=\"alert alert-danger\">";
        $Alert_message .="Reading's Don't match!!";
        $Alert_message .="</div>";
        echo $Alert_message;
    }else{

    if ($Meter_reading == $user_prevreding) {

        $Alert_message ="<div class=\"alert alert-danger\">";
        $Alert_message .="Reading for ".$Name." matched with previous reading!";
        $Alert_message .="</div>";
        echo $Alert_message;

    }else{
       // for Image
      if ($Transaction == $Trans_pin) {
        
        if (isset($_FILES['meter_picture'])) {
        $filename = $_FILES['meter_picture']['name'];
        $filetmp = $_FILES['meter_picture']['tmp_name'];
        $filetype = $_FILES['meter_picture']['type'];
        $file_extension = pathinfo($filename, PATHINFO_EXTENSION);

        $valid_extensions = array("jpg", "png", "jpeg");

            if(in_array($file_extension, $valid_extensions)){

            $new_name = rand() .'.'.$file_extension;

            $directory = "meterphoto/".$new_name;
            move_uploaded_file($filetmp, $directory);

            $stmt = $conn->prepare("insert into readings(read_user, read_value, read_month, read_file ,read_status, read_date, read_amount, read_cons) values(?,?, ?, ?,?,?,?,?)");
            $stmt->bind_param("ssssssss", $Name, $Re_reading, $Month, $directory ,$status, $DateTime, $Amount,$consumed);
            $Execute = $stmt->execute();

            if($Execute){
                
            // For updating current reading in users table
              $updatequery = "UPDATE users SET user_lreading='$Re_reading', user_ledit ='$DateTime' WHERE user_name='$Name'";
              $query = mysqli_query($conn, $updatequery);

        // For inserting notification
        $Notimessage= $Name." Bill for ".$Month." is Rs ". $Amount .".";

        $Notistmt = $conn->prepare("insert into notification(alert_name, alert_message, alert_status, alert_userstatus , alert_date, days_left) values(?,?, ?, ?,?,?)");
        $Notistmt->bind_param("ssssss", $Name, $Notimessage, $Notistatus, $Notistatus , $DateTime,$Daysremain);
        $NotiExecute = $Notistmt->execute();

        $Alert_message ="<div class=\"alert alert-success\">";
        $Alert_message .="Bill Added Successfully. Bill amount of ".$Name." for ".$Month." is Rs ". $Amount .".";
        $Alert_message .="</div>";
        echo $Alert_message;

            $Notistmt->close();
            $stmt->close();
            $conn->close();
            
            }else{

                $Alert_message ="<div class=\"alert alert-danger\">";
                $Alert_message .="Reading not added. Something went wrong on (updatequery) ";
                $Alert_message .="</div>";
                echo $Alert_message;

            $Notistmt->close();
            $stmt->close();
            $conn->close();
      } 
       }else{
                $Alert_message ="<div class=\"alert alert-danger\">";
                $Alert_message .="Please select an image file only!!";
                $Alert_message .="</div>";
                echo $Alert_message;
    }
       }else{
                $Alert_message ="<div class=\"alert alert-danger\">";
                $Alert_message .="Wrong Security pin";
                $Alert_message .="</div>";
                echo $Alert_message;
       }  
    }
    }
  }

    }



// example




<?php 
$uploadDir = 'uploads/'; 
$response = array( 
    'status' => 0, 
    'message' => 'Form submission failed, please try again.' 
); 
 
// If form is submitted 
if(isset($_POST['name']) || isset($_POST['email']) || isset($_POST['file'])){ 
    // Get the submitted form data 
    $name = $_POST['name']; 
    $email = $_POST['email']; 
     
    // Check whether submitted data is not empty 
    if(!empty($name) && !empty($email)){ 
        // Validate email 
        if(filter_var($email, FILTER_VALIDATE_EMAIL) === false){ 
            $response['message'] = 'Please enter a valid email.'; 
        }else{ 
            $uploadStatus = 1; 
             
            // Upload file 
            $uploadedFile = ''; 
            if(!empty($_FILES["file"]["name"])){ 
                 
                // File path config 
                $fileName = basename($_FILES["file"]["name"]); 
                $targetFilePath = $uploadDir . $fileName; 
                $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION); 
                 
                // Allow certain file formats 
                $allowTypes = array('pdf', 'doc', 'docx', 'jpg', 'png', 'jpeg'); 
                if(in_array($fileType, $allowTypes)){ 
                    // Upload file to the server 
                    if(move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)){ 
                        $uploadedFile = $fileName; 
                    }else{ 
                        $uploadStatus = 0; 
                        $response['message'] = 'Sorry, there was an error uploading your file.'; 
                    } 
                }else{ 
                    $uploadStatus = 0; 
                    $response['message'] = 'Sorry, only PDF, DOC, JPG, JPEG, & PNG files are allowed to upload.'; 
                } 
            } 
             
            if($uploadStatus == 1){ 
                // Include the database config file 
                include_once 'dbConfig.php'; 
                 
                // Insert form data in the database 
                $insert = $db->query("INSERT INTO form_data (name,email,file_name) VALUES ('".$name."','".$email."','".$uploadedFile."')"); 
                 
                if($insert){ 
                    $response['status'] = 1; 
                    $response['message'] = 'Form data submitted successfully!'; 
                } 
            } 
        } 
    }else{ 
         $response['message'] = 'Please fill all the mandatory fields (name and email).'; 
    } 
} 
 
// Return response 
echo json_encode($response);












?>
