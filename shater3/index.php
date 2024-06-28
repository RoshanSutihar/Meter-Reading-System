<?php  require_once('../includes/db.php');  ?>
 
<?php
 
   date_default_timezone_set("Asia/Kathmandu");
   $CurrentTime= time();
   $DateTime = strftime("%Y-%m-%d %H:%M:%S", $CurrentTime);
   $onlytime = strftime("%Y-%m-%d", $CurrentTime);
 
?>




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
 
   $user_search2 = "SELECT * FROM readings WHERE read_user = 'Shater3'";
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
 

 <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">

 <link rel="stylesheet" href="../dist/css/adminlte.min.css">

 <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>
<body >
 
 

 <nav class="main-header navbar navbar-white navbar-light">
  
 

   <form class="form-inline ml-3">
     <div class="input-group input-group-sm">
       <input class="form-control form-control-navbar" type="search" placeholder="<?php echo $onlytime ;?>" aria-label="Search" disabled>
       <div class="input-group-append">
           <i class="fas fa-calendar-alt btn btn-navbar"></i>
       </div>
     </div>
   </form>
 
  
   <ul class="navbar-nav ml-auto">
    
     <li class="nav-item dropdown">
       <a class="nav-link" data-toggle="dropdown" href="#">
         <i class="far fa-bell"></i>
         <span class="badge badge-warning navbar-badge">0 </span>
       </a>
     </li>
   </ul>
 </nav>

 <div class="content-wrapper">

   <div class="content-header">
     <div class="container-fluid">
       <div class="row mb-2">
         <div class="col-sm-6">
           <h1 class="m-0 text-dark">Details</h1>
         </div>
         <div class="col-sm-6">
           <ol class="breadcrumb float-sm-right">
             <li class="breadcrumb-item">Click on the image to view it on full screen!! </li>
           </ol>
         </div>
       </div>
     </div>
   </div>
  
 
  
   <div class="content">
     <div class="container-fluid">



     <div id="shater2">
       <div class="row">
         <div class="col-12">
           <div class="card">
             <div class="card-header">
               <h3 class="card-title">Shater 3 - Kirana Shop</h3>
             </div>
             
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
           
           </div>
          
         </div>
       </div>
     </div>
 
     </div>
   </div>
 
 </div>
 

 <aside class="control-sidebar control-sidebar-dark">
   
   <div class="p-3">
     <h5>Title</h5>
     <p>Sidebar content</p>
   </div>
 </aside>

 <footer class="main-footer">
 
   <div class="float-right d-none d-sm-inline">
     Anything you want
   </div>
  
   <strong>Copyright &copy; <script>document.write(new Date().getFullYear());</script> <a href="#">Roshan Sutihar</a>.</strong>
 </footer>
</div>

 

<script src="../plugins/jquery/jquery.min.js"></script>

<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<script src="../dist/js/adminlte.min.js"></script>


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
 

