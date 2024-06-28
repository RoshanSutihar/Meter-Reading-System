<?php  require_once('../includes/db.php');  ?>
 
<?php
 
   date_default_timezone_set("Asia/Kathmandu");
   $CurrentTime= time();
   $DateTime = strftime("%Y-%m-%d %H:%M:%S", $CurrentTime);
   $onlytime = strftime("%Y-%m-%d", $CurrentTime);
 
?>


<!-- Notification -->

<?php 

    $alert_search = "SELECT * FROM notification where alert_name='Shater2' and days_left>0";
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
 
   $user_search2 = "SELECT * FROM readings WHERE read_user = 'Shater2'";
   $User_query2 = mysqli_query($conn, $user_search2);
   $User_fetch2 = mysqli_fetch_assoc($User_query2);

?>
<!DOCTYPE html>
 
<html lang="en">
<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <meta http-equiv="x-ua-compatible" content="ie=edge">
 
 <title>All Readings</title>
 
 <!-- Font Awesome Icons -->
 <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
 <!-- Theme style -->
 <link rel="stylesheet" href="../dist/css/adminlte.min.css">
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
     <!-- Messages Dropdown Menu -->
     
     <!-- Notifications Dropdown Menu -->
     <li class="nav-item dropdown">
       <a class="nav-link" data-toggle="dropdown" href="#">
         <i class="far fa-bell"></i>
         <span class="badge badge-warning navbar-badge">0 </span>
       </a>
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
           <h1 class="m-0 text-dark">Details</h1>
         </div><!-- /.col -->
         <div class="col-sm-6">
           <ol class="breadcrumb float-sm-right">
             <li class="breadcrumb-item">Click on the image to view it on full screen!! <br> Swipe left on table to see other details!! </li>
           </ol>
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







     <div id="shater2">
       <div class="row">
         <div class="col-12">
           <div class="card">
             <div class="card-header">
               <h3 class="card-title">Shater 2 - Tailor Shop</h3>
             </div>
             <!-- /.card-header -->
             <div class="card-body table-responsive p-0" style="height: auto;">
               <table class="table table-head-fixed text-nowrap">
                 <thead>
                   <tr>
                     <th>S.N.</th>
                     <th>Status</th>
                     <th>Month</th>
                     <th>Reading</th>
                     <th>Consumed </th>
                     <th>Amount</th>
                     <th>Image</th>
                     <th>Reading taken on</th>
                   </tr>
                 </thead>
                 <tbody>
                   <?php
           if($User_query2){
             $SN = 1;
             foreach($User_query2 as $row)
                                   {
           ?>
                   <tr>
                     <td><?php echo $SN; ?></td>
                     <td><span class="badge bg-success"><?php echo $row['read_status']; ?></span></td>
                     <td><?php echo $row['read_month']; ?></td>
                     <td><?php echo $row['read_value']; ?> units</td>
                     <td><?php echo $row['read_cons']; ?> units</td>
                     <td>Rs. <?php echo $row['read_amount']; ?></td>
                     <td><?php echo "<a href=\"../". $row['read_file'] ."\" target=\"_blank\"> <img src=\"../". $row['read_file'] ."\" class=\"img-circle elevation-2\" alt=\"null\" style=\"height:40px; width:40px\" target=\"_blank\"> </a>";?></td>
                     <td><?php echo $row['read_date']; ?></td>
                   </tr>
                   <?php
                   $SN++;
                 }
                
             }
                   ?>
                 </tbody>
               </table>
             </div>
             <!-- /.card-body -->
           </div>
           <!-- /.card -->
         </div>
       </div>
       <!-- /.row -->
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
 
 <!-- Main Footer -->
 <footer class="main-footer">
   <!-- To the right -->
   <div class="float-right d-none d-sm-inline">
     Anything you want
   </div>
   <!-- Default to the left -->
   <strong>Copyright &copy; <script>document.write(new Date().getFullYear());</script> <a href="#">Roshan Sutihar</a>.</strong>
 </footer>
</div>
<!-- ./wrapper -->
 
<!-- REQUIRED SCRIPTS -->
 
<!-- jQuery -->
<script src="../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="../dist/js/adminlte.min.js"></script>


<!-- For fetching total unpaid amount -->

<script>

due_amnt();
function due_amnt(){
  $.ajax({

  url : "../ajax/fetchunpaidshater2.php",
  type: "POST",

  success: function(data){
    $('#dueamount').html(data);
  }
});
};

</script>


<!-- for unpaid details load on small card -->

<script>

$(document).ready(function(){

$('#load_details').on("click", function(e){

  $.ajax({

  url : "../ajax/fetch_unpaid_details_shater2.php",
  type: "POST",
    success: function(data){
    $('#pending_details').html(data);
    $('#pending_details').toggle();
  }
});


});
});

</script>

 
</body>
</html>
 

