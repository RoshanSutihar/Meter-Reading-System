<?php
require_once "includes/db.php";
require_once "includes/session.php";
require_once "includes/functions.php";

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/config.php";

$user_search = "SELECT * FROM users";
$User_query = mysqli_query($conn, $user_search);
$user_count = mysqli_num_rows($User_query);
$User_fetch = mysqli_fetch_assoc($User_query);

if (isset($_POST["new_registration"])) {
    $Transaction = "0605";
    $Name = $_POST["user_id"];
    $Month = $_POST["user_month"];
    $Notistatus = "unseen";
    $Daysremain = "120";
    $Status = "Unpaid";
    $divider = 5;

    $reading_search = "SELECT * FROM users WHERE user_name = '$Name'";
    $previous_query = mysqli_query($conn, $reading_search);
    $reading_fetch = mysqli_fetch_assoc($previous_query);
    $user_prevreding = $reading_fetch["user_lreading"];
    $user_rate = $reading_fetch["user_rate"];
    $user_number = $reading_fetch["user_contact"];
    $prev_remainder = $reading_fetch["remainder"];

    $Meter_reading = mysqli_real_escape_string($conn, $_POST["meter"]);
    $Re_reading = mysqli_real_escape_string($conn, $_POST["remeter"]);
    $Trans_pin = mysqli_real_escape_string($conn, $_POST["transa_pin"]);

    $consumed = $Re_reading - $user_prevreding;
    $Amount = $consumed * $user_rate;
    $totalamnt = $Amount + $prev_remainder;
    $left_remainder = $totalamnt % $divider;
    $net_amnt = $totalamnt - $left_remainder;

    $recaptcha = new \ReCaptcha\ReCaptcha(CONTACTFORM_RECAPTCHA_SECRET_KEY);
    $resp = $recaptcha->verify(
        $_POST["g-recaptcha-response"],
        $_REQUEST["REMOTE_ADDR"],
    );

    if (!$resp->isSuccess()) {
        $errors = $resp->getErrorCodes();
        $error = $errors[0];

        $recaptchaErrorMapping = [
            "missing-input-secret" => "No reCAPTCHA secret key was submitted.",
            "invalid-input-secret" =>
                "The submitted reCAPTCHA secret key was invalid.",
            "missing-input-response" => "No reCAPTCHA response was submitted.",
            "invalid-input-response" =>
                "The submitted reCAPTCHA response was invalid.",
            "bad-request" =>
                "An unknown error occurred while trying to validate your response.",
            "timeout-or-duplicate" =>
                "The request is no longer valid. Please try again.",
        ];

        $errorMessage = $recaptchaErrorMapping[$error];
        $_SESSION["ErrorMessage"] = "Please Retry captcha";
        Redirect_to("index.php");
    }

    date_default_timezone_set("Asia/Kathmandu");
    $CurrentTime = time();
    $DateTime = strftime("%Y-%m-%d %H:%M:%S", $CurrentTime);

    if (
        empty($Meter_reading) ||
        empty($Re_reading) ||
        empty($Trans_pin) ||
        empty($Month)
    ) {
        $_SESSION["ErrorMessage"] = "All Fields are required!";
        Redirect_to("index.php");
    } else {
        if ($Meter_reading !== $Re_reading) {
            $_SESSION["ErrorMessage"] = "Readings do not match";
            Redirect_to("index.php");
        } else {
            if ($Meter_reading == $user_prevreding) {
                $_SESSION["ErrorMessage"] =
                    "Reading for " . $Name . " matched with previous reading!";
                Redirect_to("index.php");
            } else {
                if ($Transaction == $Trans_pin) {
                    if (isset($_FILES["meter_picture"])) {
                        $filename = $_FILES["meter_picture"]["name"];
                        $filesize = $_FILES["meter_picture"]["size"];
                        $filetmp = $_FILES["meter_picture"]["tmp_name"];
                        $filetype = $_FILES["meter_picture"]["type"];
                        $file_extension = pathinfo(
                            $filename,
                            PATHINFO_EXTENSION,
                        );
                        $new_name = rand() . "." . $file_extension;
                        $directory = "meterphoto/" . $new_name;
                        move_uploaded_file($filetmp, $directory);

                        $stmt = $conn->prepare(
                            "INSERT INTO readings (read_user, read_value, read_month, read_file, read_date, read_amount, read_status, read_cons) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                        );
                        $stmt->bind_param(
                            "ssssssss",
                            $Name,
                            $Re_reading,
                            $Month,
                            $directory,
                            $DateTime,
                            $net_amnt,
                            $Status,
                            $consumed,
                        );
                        $Execute = $stmt->execute();

                        if ($Execute) {
                            $updatequery = "UPDATE users SET user_lreading='$Re_reading', user_ledit='$DateTime', remainder='$left_remainder' WHERE user_name='$Name'";
                            $query = mysqli_query($conn, $updatequery);

                            $Notistmt = $conn->prepare(
                                "INSERT INTO excess (excess_name, excess_month, excess_amnt, excess_date) VALUES (?, ?, ?, ?)",
                            );
                            $Notistmt->bind_param(
                                "ssss",
                                $Name,
                                $Month,
                                $left_remainder,
                                $DateTime,
                            );
                            $NotiExecute = $Notistmt->execute();

                            $lowercaseString = strtolower($Name);
                            // Send email using PHPMailer
                            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

                            try {
                                // Server settings
                                $mail->setLanguage(CONTACTFORM_LANGUAGE);
                                $mail->SMTPDebug = CONTACTFORM_PHPMAILER_DEBUG_LEVEL;
                                $mail->isSMTP();
                                $mail->Host = CONTACTFORM_SMTP_HOSTNAME;
                                $mail->SMTPAuth = true;
                                $mail->Username = CONTACTFORM_SMTP_USERNAME;
                                $mail->Password = CONTACTFORM_SMTP_PASSWORD;
                                $mail->SMTPSecure = CONTACTFORM_SMTP_ENCRYPTION;
                                $mail->Port = CONTACTFORM_SMTP_PORT;
                                $mail->CharSet = CONTACTFORM_MAIL_CHARSET;
                                $mail->Encoding = CONTACTFORM_MAIL_ENCODING;

                                // Recipients
                                $mail->setFrom(
                                    CONTACTFORM_FROM_ADDRESS,
                                    CONTACTFORM_FROM_NAME,
                                );
                                $mail->addAddress($user_number, $Name);

                                // carbon copys
                                $ccEmail = "roshansutihar@gmail.com"; // Replace with your CC email address
                                $ccName = "Roshan Sutihar"; // Optional: Replace with name of CC recipient
                                $mail->addCC($ccEmail, $ccName);

                                $cc2Email = "binitasutihar@gmail.com"; // Replace with your second CC email address
                                $cc2Name = "Binita Sutihar"; // Optional: Replace with name of second CC recipient
                                $mail->addCC($cc2Email, $cc2Name);

                                // Content
                                $mail->isHTML(true);
                                $mail->Subject =
                                    "Electricity Bill for " . $Month;
                                $mail->Body = "
    <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                background-color: #f4f4f4;
                margin: 0;
                padding: 0;
            }
            .container {
                width: 80%;
                margin: auto;
                padding: 20px;
                background-color: #ffffff;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            h2 {
                color: #333333;
                text-align: center;
            }
            p {
                margin-bottom: 15px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            th, td {
                border: 1px solid #dddddd;
                padding: 10px;
                text-align: left;
            }
            img {
                max-width: 100%;
                height: auto;
                display: block;
                margin-top: 20px;
                margin-bottom: 20px;
            }
            .footer {
                margin-top: 20px;
                text-align: center;
                color: #666666;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Electricity Bill</h2>
            <p>Dear $Name,</p>
            <p>Your electricity bill for the month of $Month is Rs. $net_amnt.</p>
            <p>Below are the details:</p>
            <table>
                <tr>
                    <th>Category</th>
                    <th>Details</th>
                </tr>
                <tr>
                    <td>Current Reading</td>
                    <td>$Re_reading</td>
                </tr>
                <tr>
                    <td>Previous Reading</td>
                    <td>$user_prevreding</td>
                </tr>
                <tr>
                    <td>Consumed Units</td>
                    <td>$consumed</td>
                </tr>
                <tr>
                    <td>Current Remainder</td>
                    <td>$left_remainder</td>
                </tr>
                <tr>
                    <td>Total Amount</td>
                    <td><strong>Rs. $net_amnt</strong></td>
                </tr>
            </table>
            <p>Thank you.</p>
            <h4> Meter Photo for this month: </h4>
            <img src='https://meterdb.roshansutihar.com.np/meterphoto/$new_name' alt='Meter Photo' />
            <div class='footer'>
            <p>Note: Remainders will be added in next months bill.</p>
            <p>Reading added on: $DateTime</p>
            </div>
        </div>
    </body>
    </html>
";

                                $mail->AltBody = "Dear $Name,\nYour electricity bill for the month of $Month is Rs. $net_amnt.\n\nCurrent Reading: $Re_reading\nPrevious Reading: $user_prevreding\nConsumed Units: $consumed\nRemainder: $prev_remainder\nTotal Amount: $net_amnt\n\nThank you.";

                                $mail->send();
                                $_SESSION["SuccessMessage"] .=
                                    " Email sent successfully.";
                            } catch (Exception $e) {
                                $_SESSION[
                                    "ErrorMessage"
                                ] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                            }

                            $_SESSION["SuccessMessage"] =
                                "Bill Added Successfully. Bill amount of " .
                                $Name .
                                " for " .
                                $Month .
                                " is Rs " .
                                $net_amnt .
                                ".";
                            Redirect_to("index.php");

                            $Notistmt->close();
                            $stmt->close();
                            $conn->close();
                        } else {
                            $_SESSION["ErrorMessage"] =
                                "Reading not added. Something went wrong on (updatequery)";
                            Redirect_to("index.php");

                            $Notistmt->close();
                            $stmt->close();
                            $conn->close();
                        }
                    }
                } else {
                    $_SESSION["ErrorMessage"] = "Wrong Security Pin!";
                    Redirect_to("index.php");
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Electricity Database</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

</head>
<body class="hold-transition register-page">

<div class="register-box">
  <div class="register-logo">
    <a href="#"><b>Add New Reading <i class="fas fa-lightbulb mr-2"></i></b></a>
  </div>
  <div class="card">
    <div class="card-body register-card-body">

    <!-- for error Message -->
      <div id="statusMsg">
      </div>
      <?php
      echo ErrorMessage();
      echo SuccessMessage();
      ?>
      <p class="login-box-msg">Enter Details below</p>
      
      <form action="<?php echo htmlentities(
          $_SERVER["PHP_SELF"],
      ); ?>" method="post" enctype="multipart/form-data" name="enter_data">
        <div class="input-group mb-3">
            <select class="custom-select" name="user_id" id="for_name" onchange="changetype(this);" required>
              <option value="">----- Select Name -----</option>
              <?php if ($User_query) {
                  foreach ($User_query as $row) { ?>
                          
            <option value="<?php echo $row["user_name"]; ?>"><?php echo $row[
    "user_name"
]; ?></option>

              <?php }
              } ?>
            </select>
            <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-user-tag"></span>
                </div>
            </div>
        </div>
        <div class="input-group mb-3">
          <input type="number" class="form-control" step="0.01" placeholder="Enter meter reading" name="meter" id="changecolorsecond" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-tachometer-alt"></span>
            </div>
          </div>
        </div>
       
        <div class="input-group mb-3" >
          <input type="number" class="form-control" name="remeter" placeholder="Re-enter meter reading" id="changecolor" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-tachometer-alt"></span>
            </div>
          </div>
        </div>

        <!-- Error message javascript -->

        <div class = "input-group mb-3 " id="showerror" style="display:none;" >
          <span id="reading_error"></span>
        </div>

        <!-- /. Error message javascript -->

        <div class="input-group mb-3" id="ourmeter1" style="display: none;">
          <input type="number" class="form-control" name="ourmeter"  placeholder="Our consumed unit">
          <span style="color:red;"> Check our reading first then input our consumed unit here!!</span>
        </div>

      <!-- /. 
      
      FOR CHANGING THE YEAR ******** CHANGE IN THE VALUE AS WELL AS THE OPTION *********
      
      t -->

        <div class="input-group mb-3">
            <select class="custom-select" name="user_month" id="search_valid" required>
                          <option value="">----- Select Month -----</option>
                          <option value="Baisakh 2081">Baisakh 2081</option>
                          <option value="Jestha 2081">Jestha 2081</option>
                          <option value="Ashad 2081">Ashad 2081</option>
                          <option value="Shrawan 2081">Shrawan 2081</option>
                          <option value="Bhadra 2081">Bhadra 2081</option>
                          <option value="Ashwin 2081">Ashwin 2081</option>
                          <option value="Kartik 2081">Kartik 2081</option>
                          <option value="Mangsir 2081">Mangsir 2081</option>
                          <option value="Poush 2081">Poush 2081</option>
                          <option value="Magh 2081">Magh 2081</option>
                          <option value="Falgun 2081">Falgun 2081</option>
                          <option value="Chaitra 2081">Chaitra 2081</option>
            </select>
            <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-calendar-alt"></span>
                </div>
            </div>
        </div>
        
        <div class="input-group mb-3">     
        <input type="file" name= "meter_picture" required> 
        </div>

        <div class="input-group mb-3">
          <input type="password" class="form-control" name="transa_pin" placeholder="Security Pin 4 digit"  maxlength="4">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>

        <div class="row mb-2">
            <div class="form-group text-center">
                <div class="g-recaptcha" data-sitekey="<?= CONTACTFORM_RECAPTCHA_SITE_KEY ?>"></div>
            </div>
            <button type="submit" name="new_registration" class="btn btn-primary btn-block ">Add Reading!!</button>
        </div>
        <div class = "row">
        <div class="input-group mb-3 mt-2">     
       <a href= "dashboard.php" class="btn btn-success btn-block"> Dashboard</a>
        </div>
        </div>
      </form>
      
    </div>
    <!-- /.form-box -->
  </div><!-- /.card -->
</div>
<!-- /.register-box -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>


<script type="text/javascript">

var meter = document.forms['enter_data']['meter'];

var reemeter = document.forms['enter_data']['remeter'];


  function validatedata(){
    if(meter.value === reemeter.value){
    document.getElementById("reading_error").textContent = "Readings matched !";
    document.getElementById("reading_error").style.color = "green";
    document.getElementById('showerror').style.display = "block";
    document.getElementById('showerror').style.textAlign = "center";
    document.getElementById("changecolor").classList.add("is-valid");
    document.getElementById("changecolor").classList.remove("is-invalid");
    document.getElementById("changecolorsecond").classList.add("is-valid");
    document.getElementById("changecolorsecond").classList.remove("is-invalid");
    setTimeout(function(){document.getElementById('showerror').style.display = "none";}, 1500);
  }

  if (meter.value != reemeter.value) {
    document.getElementById("reading_error").textContent = "Readings don't match !";
    document.getElementById("reading_error").style.color = "red";
    document.getElementById('showerror').style.display = "block";
    document.getElementById('showerror').style.textAlign = "center";
    document.getElementById("changecolor").classList.add("is-invalid");
    document.getElementById("changecolor").classList.remove("is-valid");
    document.getElementById("changecolorsecond").classList.add("is-invalid");
    document.getElementById("changecolorsecond").classList.remove("is-valid");
    setTimeout(function(){document.getElementById('showerror').style.display = "none";}, 1500);
  } 

  

}
document.enter_data.remeter.addEventListener("keyup", validatedata);
</script>


<script>

function changetype(select){
   if(select.value == "Main Meter"){
    document.getElementById('ourmeter1').style.display = "block";
   } else{
       document.getElementById('ourmeter1').style.display = "none";
   }
}
</script>



<script>
$('#search_valid').on("change", function fetchdata(){
  var month = $('#search_valid').val();
  var name = $('#for_name').val();
$.ajax({

url : "ajax/test.php",
type: "POST",
data: {search_name: name, search_month: month},
success: function(data){
  $('#statusMsg').fadeIn().html(data);
        setTimeout(function(){  
             $('#statusMsg').fadeOut("Slow");  
           }, 6000); 
}
});

});

</script>



</body>
</html>
