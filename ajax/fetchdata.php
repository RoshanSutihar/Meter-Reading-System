<?php  require_once('../includes/db.php');  ?>
<?php  require_once('../includes/session.php');  ?>
<?php  require_once('../includes/functions.php');  ?>

<?php

    if (isset($_POST['search_name'])) {
    $Name= mysqli_real_escape_string($conn, $_POST['search_name']);
    $output="";
    $SN =1;

    $user_search = "SELECT * FROM readings WHERE read_user = '$Name'";
    $User_query = mysqli_query($conn, $user_search);
    $User_fetch = mysqli_fetch_assoc($User_query);

    if(mysqli_num_rows($User_query) > 0){
    $output = '<div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">'.$Name.'</h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body table-responsive p-0" style="height: auto;">
          <table class="table table-head-fixed text-nowrap">
            <thead>
              <tr>
                <th>S.N.</th>
                <th>Name</th>
                <th>Reading</th>
                <th>Consumed</th>
                <th>Amount</th>
                <th>Month</th>
                <th>Image</th>
                <th>Status</th>
                <th>Reading taken on</th>
                <th>Paid</th>
              </tr>
            </thead>
            <tbody>';
            foreach($User_query as $row){ 
                $output .= '<tr>
                <td>'. $SN.'</td>
                <td>'. $row['read_user'].'</td>
                <td>'.$row['read_value'].' units</td>
                <td>'. $row['read_cons'].' units</td>
                <td>Rs. '.$row['read_amount'].'</td>
                <td>'.$row['read_month'].'</td>
                <td><a href="'. $row['read_file'] .'" target="_blank"> <img src="'. $row['read_file'] .'" class="img-circle elevation-2" alt="null" style="height:40px; width:40px" target="_blank"> </a></td>
                <td><span class="badge bg-success">'. $row['read_status'].'</span></td>
                <td>'.$row['read_date'].'</td>
                <td><button class="btn btn-danger" id="paid-btn" data-id="'. $row['read_id'].'"><i class="fas fa-check"></i></button></td>
              </tr>';
                $SN++;
            }

            $output .= "</tbody>
            </table>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
      </div>
    </div>";
    echo $output;
    }
}
?>