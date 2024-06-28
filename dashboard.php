<?php  require_once('includes/db.php');  ?>

<?php

    date_default_timezone_set("Asia/Kathmandu");
    $CurrentTime= time();
    $DateTime = strftime("%Y-%m-%d %H:%M:%S", $CurrentTime);
    $onlytime = strftime("%Y-%m-%d", $CurrentTime);

?>


<!-- NOTIFICATION -->



<?php 

    $alert_search = "SELECT * FROM notification where days_left>0";
    $alert_query = mysqli_query($conn, $alert_search);
    $alert_count = mysqli_num_rows($alert_query);
    $alert_fetch = mysqli_fetch_assoc($alert_query);
    if($alert_count>0){
        $alert_value = $alert_count;
    } 
    else{
        $alert_value = 0;
    }
?>


<?php 

    $user_search = "SELECT * FROM users";
    $User_query = mysqli_query($conn, $user_search);
    $user_count = mysqli_num_rows($User_query);
    $User_fetch = mysqli_fetch_assoc($User_query);
    $user_dbname = $User_fetch['user_name'];
    $user_prevreding = $User_fetch['user_lreading'];
?>


<!DOCTYPE html>

<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <title>All Readings</title>

  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>
<body >


  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button" active><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Home</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li>
    </ul>

    <!-- SEARCH FORM -->
    <form class="form-inline ml-3">
      <div class="input-group input-group-sm">
        <input class="form-control form-control-navbar" type="search" placeholder="<?php echo $onlytime ;?>" aria-label="Search" disabled>
        <div class="input-group-append"> 
            <i class="fas fa-calendar-alt btn btn-navbar"></i>
        </div>
      </div>
    </form>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Notifications Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="fas fa-bell"></i>
          <span class="badge badge-danger navbar-badge"><?php echo $alert_value; ?></span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-header"><?php echo $alert_value; ?> Notifications</span>
          <?php
           if($alert_query){
             foreach($alert_query as $row){
           ?>

         <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
           <p><i class="fas fa-lightbulb mr-2"></i><?php echo $row['alert_message']; ?> <span class="float-right text-muted text-sm">Expires in <?php echo $row['days_left']; ?> hrs!</span></p>
           
         </a>

         <?php
                 }
                
             }
                   ?>


          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer"><p>Notification automatically expire!</p></a>
        </div>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">Meter Reading's History</h1>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">

      <div class="row">
          <div class="col-lg-3 col-12">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3> Rs. <span id="dueamount"> </span></h3>
                <h5>Total Pending Amount</h5>
              </div>
              <div class="icon">
                <i class="ion ion-person-add"></i>
              </div>
              <a id="load_details" class="small-box-footer">Double click for more info <i class="fas fa-arrow-circle-right"></i></a>
              <div id="pending_details">
              </div>
            </div>
          </div>
          <!-- ./col -->
        </div>
             <!-- ./row total-->

        <div class="row">
          <div class="col-lg-6">
            <div class="card card-primary card-outline">
              <div class="card-body">
                <div id="message">
                </div>
                <p class="card-text">
                  Select the room below and <b>wait for data to load</b> to see the all the meter reading history!
                </p>
                <div class="form-group">
                  
                  <select class="form-control select2" id="search_name" style="width: 100%;">
                    <option value="0">--- Select Name ---</option>
               <?php
            if($User_query){
              foreach($User_query as $row)
                                    { 
            ?>               
          <option value="<?php echo $row['user_name']; ?>"><?php echo $row['user_name']; ?></option>
           <?php
              }
            }
          ?>
          </select>
                </div>
              </div>
            </div><!-- /.card -->
          </div>
          <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->

        <!-- Success message -->
        <div class="message">
        </div>

        <!-- data display -->
        <div id="display_data">
        </div>

      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
    <div class="p-3">
      <h5>Title</h5>
      <p>Sidebar content</p>
    </div>
  </aside>
  <!-- /.control-sidebar -->

  
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>


<!-- For fetching total unpaid amount -->

<script>

due_amnt();
function due_amnt(){
  $.ajax({

  url : "ajax/fetchunpaid.php",
  type: "POST",

  success: function(data){
    $('#dueamount').html(data);
  }
});
};

</script>

<!-- for fetching table with all details -->

<script>

$('#search_name').on("change", function fetchdata(){
  var name = $('#search_name').val();
$.ajax({

url : "ajax/fetchdata.php",
type: "POST",
data: {search_name: name},
success: function(data){
  $('#display_data').html(data);
}
});

});

</script>

<!-- For marking paid -->

<script type="text/javascript">
  
  var myVar;

$(document).on('click', '#paid-btn',function(){

  var conf = confirm('Mark this bill as paid. Once marked it can\'t be changed!!');
  var paid_id = $(this).data("id");
  if (conf == true) {
    $.ajax({
      url: "ajax/mark_paid.php",
      type: "POST",
      data: {change_id: paid_id},
      success: function(output){
        $('#display_data').load(location.href + " #display_data");
        $('#search_name').val('0');
        due_amnt();
        $('#message').fadeIn().html(output);
        setTimeout(function(){  
             $('#message').fadeOut("Slow");  
           }, 5000);  
      }
    });
  }
});

</script>

<!-- for unpaid details load on small card -->

<script>

$(document).ready(function(){

$('#load_details').on("click", function(e){

  $.ajax({

  url : "ajax/fetch_unpaid_details.php",
  type: "POST",
    success: function(data){
    $('#pending_details').html(data);
    $('#pending_details').toggle();
  }
});


});
});

</script>


<!-- AJAX -->
</body>
</html>
