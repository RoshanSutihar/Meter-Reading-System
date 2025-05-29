<?php
require_once "includes/db.php";
require_once "includes/session.php";
require_once "includes/functions.php";
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/config.php";
header("Content-Type: application/json");
// Function to handle POST request
function handlePostRequest() {
    global $conn; // Ensure $conn is available in this scope

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $Transaction = "0605";
    $Name = $_POST["name"];
    $Month = $_POST["month"];
    $Notistatus = "unseen";
    $Daysremain = "120";
    $Status = "Unpaid";
    $divider = 5;

    $reading_search = "SELECT * FROM users WHERE user_name = '$Name'";
    $previous_query = mysqli_query($conn, $reading_search);
    $reading_fetch = mysqli_fetch_assoc($previous_query);
    $user_prevreading = $reading_fetch["user_lreading"];
    $user_rate = $reading_fetch["user_rate"];
    $user_number = $reading_fetch["user_contact"];
    $prev_remainder = $reading_fetch["remainder"];

    $Meter_reading = mysqli_real_escape_string($conn, $_POST["reading"]);
    $Re_reading = mysqli_real_escape_string($conn, $_POST["rereading"]);
    $Trans_pin = mysqli_real_escape_string($conn, $_POST["pin"]);

    $consumed = $Re_reading - $user_prevreading;
    $Amount = $consumed * $user_rate;
    $totalamnt = $Amount + $prev_remainder;
    $left_remainder = $totalamnt % $divider;
    $net_amnt = $totalamnt - $left_remainder;

    date_default_timezone_set("Asia/Kathmandu");
    $CurrentTime = time();
    $DateTime = strftime("%Y-%m-%d %H:%M:%S", $CurrentTime);

    if (empty($Meter_reading) || empty($Re_reading) || empty($Trans_pin) || empty($Month)) {
        http_response_code(400);
        echo json_encode(["error" => "All Fields are required!"], JSON_UNESCAPED_UNICODE);
        return;
    }

    if ($Meter_reading !== $Re_reading) {
        http_response_code(400);
        echo json_encode(["error" => "Readings do not match"], JSON_UNESCAPED_UNICODE);
        return;
    }

    if ($Meter_reading == $user_prevreading) {
        http_response_code(400);
        echo json_encode(["error" => "Reading for $Name matched with previous reading!"], JSON_UNESCAPED_UNICODE);
        return;
    }

    if ($Transaction == $Trans_pin) {
        if (isset($_FILES["meter_picture"])) {
            $filename = $_FILES["meter_picture"]["name"];
            $filesize = $_FILES["meter_picture"]["size"];
            $filetmp = $_FILES["meter_picture"]["tmp_name"];
            $filetype = $_FILES["meter_picture"]["type"];
            $file_extension = pathinfo($filename, PATHINFO_EXTENSION);
            $new_name = rand() . "." . $file_extension;
            $directory = "meterphoto/" . $new_name;
            move_uploaded_file($filetmp, $directory);

            $stmt = $conn->prepare(
                "INSERT INTO readings (read_user, read_value, read_month, read_file, read_date, read_amount, read_status, read_cons) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
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
                $consumed
            );
            $Execute = $stmt->execute();

            if ($Execute) {
                $updatequery = "UPDATE users SET user_lreading='$Re_reading', user_ledit='$DateTime', remainder='$left_remainder' WHERE user_name='$Name'";
                $query = mysqli_query($conn, $updatequery);

                $Notistmt = $conn->prepare(
                    "INSERT INTO excess (excess_name, excess_month, excess_amnt, excess_date) VALUES (?, ?, ?, ?)"
                );
                $Notistmt->bind_param(
                    "ssss",
                    $Name,
                    $Month,
                    $left_remainder,
                    $DateTime
                );
                $NotiExecute = $Notistmt->execute();

                http_response_code(201);
                echo json_encode(["message" => "Bill Added Successfully."], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Failed to add reading."], JSON_UNESCAPED_UNICODE);
            }
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Wrong Security Pin!"], JSON_UNESCAPED_UNICODE);
    }
}





// a function to fetch the name of months 



function getMonthsList() {
    $year = 2082; // Example year
    $months = [];
    $monthNames = [
        "Baisakh", "Jestha", "Ashad", "Shrawan", "Bhadra", "Ashwin",
        "Kartik", "Mangsir", "Poush", "Magh", "Falgun", "Chaitra"
    ];

    foreach ($monthNames as $month) {
        $months[] = "$month $year";
    }
http_response_code(200);
    echo json_encode($months, JSON_UNESCAPED_UNICODE);
}



// Function to handle GET request
function handleGetAllUsersRequest() {
    global $conn; // Ensure $conn is available in this scope

    $user_search = "SELECT user_id, user_name FROM users";
    $User_query = mysqli_query($conn, $user_search);

    if (!$User_query) {
        http_response_code(500);
        echo json_encode(["error" => "Database query failed: " . mysqli_error($conn)], JSON_UNESCAPED_UNICODE);
        return;
    }

    $users = [];
    while ($user = mysqli_fetch_assoc($User_query)) {
        $users[] = $user;
    }

    http_response_code(200);
    echo json_encode($users, JSON_UNESCAPED_UNICODE);
}

// Function to handle GET request for a specific user by ID
function handleGetUserByIdRequest($id) {
    global $conn;

    $user_search = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($user_search);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        http_response_code(404);
        echo json_encode(["error" => "User not found."], JSON_UNESCAPED_UNICODE);
        return;
    }

    $user = $result->fetch_assoc();
    http_response_code(200);
    echo json_encode($user, JSON_UNESCAPED_UNICODE);
}



// Determine the request method and URL
$request_method = $_SERVER["REQUEST_METHOD"];
$request_uri = explode('/', trim($_SERVER["REQUEST_URI"], '/'));

// Route the request
if ($request_method === "GET") {
    if (count($request_uri) === 2 && $request_uri[1] === "users") {
        handleGetAllUsersRequest();
    } elseif (count($request_uri) === 3 && $request_uri[1] === "users") {
        $user_id = intval($request_uri[2]);
        handleGetUserByIdRequest($user_id);
    } elseif (count($request_uri) === 2 && $request_uri[1] === "getmonths") {
        getMonthsList();
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Endpoint not found"], JSON_UNESCAPED_UNICODE);
    }
} elseif ($request_method === "POST") {
    handlePostRequest();
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"], JSON_UNESCAPED_UNICODE);
}


?>
