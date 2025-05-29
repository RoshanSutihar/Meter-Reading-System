<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<?php
require_once "includes/db.php";
require_once "includes/session.php";
require_once "includes/functions.php";

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/config.php";

// Add FPDF library
require('fpdf/fpdf.php');

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
    $user_prevphoto = $reading_fetch["user_lphoto"]; // Get previous photo path

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
                            // Update user table with new reading AND new photo path
                            $updatequery = "UPDATE users SET user_lreading='$Re_reading', user_ledit='$DateTime', remainder='$left_remainder', user_lphoto='$directory' WHERE user_name='$Name'";
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
                            
                           
// Create PDF version of the bill using FPDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Title
$pdf->Cell(0, 10, "Electricity Bill - $Month", 0, 1, 'C');
$pdf->Ln(10);

// Customer Info
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, "Customer Name: $Name", 0, 1);
$pdf->Cell(0, 10, "Reading Date|Time: $DateTime", 0, 1);

// Bill Details Table
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(95, 10, 'Description', 1, 0, 'C');
$pdf->Cell(95, 10, 'Details', 1, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(95, 10, 'Current Reading', 1, 0);
$pdf->Cell(95, 10, "$Re_reading unit", 1, 1);

$pdf->Cell(95, 10, 'Previous Reading', 1, 0);
$pdf->Cell(95, 10, "$user_prevreding unit", 1, 1);

$pdf->Cell(95, 10, 'Consumed Units', 1, 0);
$pdf->Cell(95, 10, "$consumed unit(s)", 1, 1);

$pdf->Cell(95, 10, 'Rate per Unit', 1, 0);
$pdf->Cell(95, 10, "Rs. $user_rate", 1, 1);

$pdf->Cell(95, 10, 'Current Remainder', 1, 0);
$pdf->Cell(95, 10, "Rs. $left_remainder", 1, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(95, 10, 'Total Amount Due', 1, 0);
$pdf->Cell(95, 10, "Rs. $net_amnt", 1, 1);

// Add meter photos to PDF - Side by Side with proper spacing

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, '************************************************************************************************************************', 0, 1, 'C');

// Calculate positions for side-by-side images
$pageWidth = $pdf->GetPageWidth();
$margin = 15; // Reduced margin for more space
$availableWidth = $pageWidth - (2 * $margin);
$imageWidth = ($availableWidth / 2) - 5; // Each image gets half width minus spacing
$imageHeight = 100; // Fixed height for consistency
// Get current Y position
$yPos = $pdf->GetY();

// Previous Photo (left side)
if (!empty($user_prevphoto)) {
    // Label for previous reading
    $pdf->SetXY($margin, $yPos);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($imageWidth, 5, 'Previous Reading', 0, 0, 'C');
    
    // Image for previous reading
    $pdf->Image($user_prevphoto, $margin, $yPos + 7, $imageWidth, $imageHeight);
}

// Current Photo (right side)
// Label for current reading
$pdf->SetXY($margin + $imageWidth + 10, $yPos);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($imageWidth, 5, 'Current Reading', 0, 0, 'C');

// Image for current reading
$pdf->Image($directory, $margin + $imageWidth + 10, $yPos + 7, $imageWidth, $imageHeight);

// Move Y position down below the images
$pdf->SetY($yPos + $imageHeight + 15);

// Footer
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, '************************************************************************************************************************', 0, 1, 'C');
$pdf->SetFont('Arial', 'I', 10);

$pdf->Cell(0, 10, 'Any remaining balance will be carried over to next month\'s bill.', 0, 1, 'C');

$pdfFileName = "Electricity_Bill_" . $Name . "_" . str_replace(" ", "_", $Month) . ".pdf";
$pdfFilePath = __DIR__ . "/pdf_bills/" . $pdfFileName;

                            
                            // Ensure directory exists
                            if (!file_exists(__DIR__ . "/pdf_bills")) {
                                mkdir(__DIR__ . "/pdf_bills", 0777, true);
                            }
                            
                            $pdf->Output($pdfFilePath, 'F');

                            // Send email using PHPMailer with PDF attachment
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

                                $bccEmail = "roshansutihar@gmail.com"; 
                                $bccName = "Roshan Sutihar"; 
                                $mail->addBCC($bccEmail, $bccName);

                                 $bcc2Email = "binitasutihar@gmail.com"; 
                                $bcc2Name = "Binita Sutihar"; 
                                $mail->addBCC($bcc2Email, $bcc2Name);

                                // Attach PDF
                                $mail->addAttachment($pdfFilePath, $pdfFileName);

                                // Content
                                $mail->isHTML(true);
                                $mail->Subject =
                                    $Name. " Electricity Bill for " . $Month;
                                $mail->Body = "
    <html>
<head>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

        body {
            font-family: 'Roboto', Arial, sans-serif;
            line-height: 1.6;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #4A90E2, #50C9C3);
            padding: 20px;
            border-radius: 8px 8px 0 0;
            color: #ffffff;
            text-align: center;
        }
        .header h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        h3 {
            color: #333333;
            font-weight: 500;
            margin-bottom: 10px;
        }
        p {
            margin-bottom: 15px;
            color: #666666;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #4A90E2;
            color: #ffffff;
            padding: 10px;
            font-weight: 500;
        }
        td {
            border: 1px solid #dddddd;
            padding: 12px;
            text-align: left;
            color: #555555;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        img {
            max-width: 100%;
            height: auto;
            display: block;
            margin-top: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .footer {
            background: linear-gradient(135deg, #50C9C3, #4A90E2);
            padding: 15px;
            border-radius: 0 0 8px 8px;
            text-align: center;
            color: #ffffff;
            font-size: 14px;
        }
        .footer p {
            margin: 5px 0;
        }
        
        .photo-section {
            margin: 20px 0;
        }
        .photo-label {
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Electricity Bill $Month</h2>
        </div>
        <h3>$Name,</h3>
        <p>Your electricity bill for the month of $Month is <strong>Rs. $net_amnt</strong>.</p>
        <p>Below are the details:</p>
        <table>
            <tr>
                <th>Category</th>
                <th>Details</th>
            </tr>
            <tr>
                <td>Current Reading</td>
                <td>$Re_reading unit</td>
            </tr>
            <tr>
                <td>Previous Reading</td>
                <td>$user_prevreding unit</td>
            </tr>
            <tr>
                <td>Consumed Units</td>
                <td>$consumed unit(s)</td>
            </tr>
            <tr>
                <td>Current Remainder</td>
                <td>Rs. $left_remainder</td>
            </tr>
            <tr>
                <td>Total Amount</td>
                <td><strong>Rs. $net_amnt</strong></td>
            </tr>
        </table>
        ";
                                
                                // Add previous photo if exists
                                if (!empty($user_prevphoto)) {
                                    $mail->Body .= "
        <div class='photo-section'>
            <div class='photo-label'>Previous Meter Reading Photo:</div>
            <img src='https://meterdb.roshansutihar.com.np/$user_prevphoto' alt='Previous Meter Photo' />
        </div>";
                                }
                                
                                // Add current photo
                                $mail->Body .= "
        <div class='photo-section'>
            <div class='photo-label'>Current Meter Reading Photo:</div>
            <img src='https://meterdb.roshansutihar.com.np/$directory' alt='Current Meter Photo' />
        </div>
        
        <p>Note: Any remaining balance will be carried over to next month's bill.</p>
        
        <p class='header'>Reading added on:<br> $DateTime</p>
    </div>
</body>
</html>
";

                                $mail->AltBody = "Dear $Name,\nYour electricity bill for the month of $Month is Rs. $net_amnt.\n\nCurrent Reading: $Re_reading\nPrevious Reading: $user_prevreding\nConsumed Units: $consumed\nRemainder: $prev_remainder\nTotal Amount: $net_amnt\n\nA PDF version of this bill is attached to this email.\n\nThank you.";

                                $mail->send();
                                $_SESSION["SuccessMessage"] .=
                                    " Email with PDF attachment sent successfully.";
                                
                                // Delete the PDF file after sending
                                unlink($pdfFilePath);
                                
                            } catch (Exception $e) {
                                $_SESSION[
                                    "ErrorMessage"
                                ] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                                
                                // Delete the PDF file if email failed
                                if (file_exists($pdfFilePath)) {
                                    unlink($pdfFilePath);
                                }
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
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add New Reading</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #4361ee;
      --secondary-color: #3f37c9;
      --accent-color: #4895ef;
      --light-color: #f8f9fa;
      --dark-color: #212529;
    }
    
    body {
      background-color: #f5f7fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
    }
    
    .register-container {
      width: 100%;
      max-width: 400px;
      margin: 20px auto;
      margin-top:20p;
    }
    
    .register-card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 6px 15px rgba(0,0,0,0.08);
      overflow: hidden;
    }
    
    .card-header {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 20px;
      border-bottom: none;
      text-align: center;
    }
    
    .card-header h3 {
      margin: 0;
      font-weight: 600;
    }
    
    .card-body {
      padding: 30px;
    }
    
    .input-group-text {
      background-color: var(--light-color);
      border: 1px solid #e0e0e0;
    }
    
    .form-control, .form-select {
      border-radius: 8px;
      padding: 10px 15px;
      border: 1px solid #e0e0e0;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--accent-color);
      box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
    }
    
    .btn-primary {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      border: none;
      padding: 10px;
      font-weight: 500;
    }
    
    .btn-primary:disabled {
      opacity: 0.65;
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    }
    
    .btn-success {
      background: linear-gradient(135deg, #4caf50, #2e7d32);
      border: none;
      padding: 10px;
      font-weight: 500;
    }
    
    #showerror {
      margin-top: -10px;
      margin-bottom: 15px;
    }
    
    .login-box-msg {
      color: #666;
      margin-bottom: 25px;
      font-size: 1rem;
    }
    
    .file-input {
      display: block;
      width: 100%;
      padding: 10px;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      background-color: white;
    }
    
    .is-valid {
      border-color: #28a745 !important;
    }
    
    .is-invalid {
      border-color: #dc3545 !important;
    }
  </style>
</head>
<body>
  <div class="register-container">
    <div class="register-card">
      <div class="card-header">
        <h3><i class="fas fa-lightbulb me-2"></i>Add New Reading</h3>
      </div>
      <div class="card-body">
        <!-- for error Message -->
        <div id="statusMsg"></div>
        <?php
        echo ErrorMessage();
        echo SuccessMessage();
        ?>
        
        <form action="<?php echo htmlentities($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" name="enter_data">
          <div class="input-group mb-3">
            <select class="form-select" name="user_id" id="for_name" onchange="changetype(this);" required>
              <option value="">----- Select Name -----</option>
              <?php if ($User_query) {
                foreach ($User_query as $row) { ?>
                  <option value="<?php echo $row["user_name"]; ?>"><?php echo $row["user_name"]; ?></option>
                <?php }
              } ?>
            </select>
            <span class="input-group-text">
              <i class="fas fa-user-tag"></i>
            </span>
          </div>
          
          <div class="input-group mb-3">
            <input type="number" class="form-control" placeholder="Enter meter reading" name="meter" id="changecolorsecond" required>
            <span class="input-group-text">
              <i class="fas fa-tachometer-alt"></i>
            </span>
          </div>
          
          <div class="input-group mb-3">
            <input type="number" class="form-control" name="remeter" placeholder="Re-enter meter reading" id="changecolor" required>
            <span class="input-group-text">
              <i class="fas fa-tachometer-alt"></i>
            </span>
          </div>
          
          <!-- Error message javascript -->
          <div class="input-group mb-3" id="showerror" style="display:none;">
            <span id="reading_error" class="w-100 text-center"></span>
          </div>
          
          <div class="input-group mb-3" id="ourmeter1" style="display: none;">
            <input type="number" class="form-control" name="ourmeter" placeholder="Our consumed unit">
            <small class="text-muted mt-1">Check our reading first then input our consumed unit here!!</small>
          </div>
          
          <div class="input-group mb-3">
            <select class="form-select" name="user_month" id="search_valid" required>
              <option value="">----- Select Month -----</option>
              <option value="Baisakh 2082">Baisakh 2082</option>
              <option value="Jestha 2082">Jestha 2082</option>
              <option value="Ashad 2082">Ashad 2082</option>
              <option value="Shrawan 2082">Shrawan 2082</option>
              <option value="Bhadra 2082">Bhadra 2082</option>
              <option value="Ashwin 2082">Ashwin 2082</option>
              <option value="Kartik 2082">Kartik 2082</option>
              <option value="Mangsir 2082">Mangsir 2082</option>
              <option value="Poush 2082">Poush 2082</option>
              <option value="Magh 2082">Magh 2082</option>
              <option value="Falgun 2082">Falgun 2082</option>
              <option value="Chaitra 2082">Chaitra 2082</option>
            </select>
            <span class="input-group-text">
              <i class="fas fa-calendar-alt"></i>
            </span>
          </div>
          
          <div class="mb-3">
            <input type="file" class="file-input form-control" name="meter_picture" required placeholder="Select meter image">
          </div>
          
          <div class="input-group mb-4">
            <input type="password" class="form-control" name="transa_pin" placeholder="Security Pin 4 digit" maxlength="4">
            <span class="input-group-text">
              <i class="fas fa-lock"></i>
            </span>
          </div>
          
          <div class="mb-3">
            <div class="g-recaptcha mb-3" data-sitekey="<?= CONTACTFORM_RECAPTCHA_SITE_KEY ?>"></div>
            <button type="submit" name="new_registration" id="submitBtn" class="btn btn-primary btn-block" disabled>Add Reading</button>
          </div>
          
          <div class="mt-2">
            <a href="dashboard.php" class="btn btn-success btn-block">See all readings</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>

  <script type="text/javascript">
    var meter = document.forms['enter_data']['meter'];
    var reemeter = document.forms['enter_data']['remeter'];

    function validatedata(){
      if(meter.value === reemeter.value){
        document.getElementById("reading_error").textContent = "Readings matched!";
        document.getElementById("reading_error").style.color = "green";
        document.getElementById('showerror').style.display = "block";
        document.getElementById("changecolor").classList.add("is-valid");
        document.getElementById("changecolor").classList.remove("is-invalid");
        document.getElementById("changecolorsecond").classList.add("is-valid");
        document.getElementById("changecolorsecond").classList.remove("is-invalid");
        setTimeout(function(){document.getElementById('showerror').style.display = "none";}, 1500);
        return true;
      }

      if (meter.value != reemeter.value) {
        document.getElementById("reading_error").textContent = "Readings don't match!";
        document.getElementById("reading_error").style.color = "red";
        document.getElementById('showerror').style.display = "block";
        document.getElementById("changecolor").classList.add("is-invalid");
        document.getElementById("changecolor").classList.remove("is-valid");
        document.getElementById("changecolorsecond").classList.add("is-invalid");
        document.getElementById("changecolorsecond").classList.remove("is-valid");
        setTimeout(function(){document.getElementById('showerror').style.display = "none";}, 1500);
        return false;
      } 
    }
    document.enter_data.remeter.addEventListener("keyup", validatedata);

    function changetype(select){
      if(select.value == "Main Meter"){
        document.getElementById('ourmeter1').style.display = "block";
      } else {
        document.getElementById('ourmeter1').style.display = "none";
      }
    }

    $('#search_valid').on("change", function fetchdata(){
      var month = $('#search_valid').val();
      var name = $('#for_name').val();
      $.ajax({
        url: "ajax/test.php",
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

    // Get form elements
    const form = document.forms['enter_data'];
    const submitBtn = document.getElementById('submitBtn');
    const userSelect = document.getElementById('for_name');

    // Function to check if all required fields are filled
    function checkFormValidity() {
      const inputs = form.querySelectorAll('input[required], select[required]');
      let allValid = true;
      
      inputs.forEach(input => {
        if (!input.value.trim()) {
          allValid = false;
        }
      });
      
      // Additional check for meter readings match
      if (meter.value !== reemeter.value) {
        allValid = false;
      }
      
      // Enable/disable button and update text
      if (allValid && userSelect.value) {
        submitBtn.disabled = false;
        submitBtn.textContent = `Add Reading for ${userSelect.value}`;
      } else {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Add Reading';
      }
    }

    // Add event listeners to all form inputs
    form.querySelectorAll('input, select').forEach(element => {
      element.addEventListener('input', checkFormValidity);
      element.addEventListener('change', checkFormValidity);
    });

    // Also call checkFormValidity when meter readings are validated
    document.enter_data.remeter.addEventListener("keyup", function() {
      validatedata();
      checkFormValidity();
    });

    // Initial check
    checkFormValidity();
  </script>
</body>
</html>