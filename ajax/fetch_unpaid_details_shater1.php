<?php  require_once('../includes/db.php');  ?>
<?php  require_once('../includes/session.php');  ?>
<?php  require_once('../includes/functions.php');  ?>

<?php

    $details="";
    $SN =1;

    $details_search = "SELECT * FROM readings WHERE read_status = 'Unpaid' AND read_user = 'Shater1'";
    $details_query = mysqli_query($conn, $details_search);
    $details_fetch = mysqli_fetch_assoc($details_query);

    if(mysqli_num_rows($details_query) > 0){
        $details = '<div class="row">
        <div class="col-12">
              <table class="table text-nowrap" style="height: 20px;">
                <thead style="background-color:transparent;">
                  <tr>
                    <th>S.N.</th>
                    <th>Name</th>
                    <th>Month</th>
                    <th>Amount</th>
                  </tr>
                </thead>
                <tbody>';
                foreach($details_query as $row){ 
                    $details .= '<tr>
                    <td>'. $SN.'</td>
                    <td>'. $row['read_user'].'</td>
                    <td>'.$row['read_month'].'</td>
                    <td>Rs. '.$row['read_amount'].'</td>
                  </tr>';
                    $SN++;
                }
                $details .= "</tbody>
                </table>
          </div>
        </div>";
        echo $details;
    }else{
      echo " All dues has been cleared!! ";
    }

?>