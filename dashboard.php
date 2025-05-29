<?php 
require_once('includes/db.php');

date_default_timezone_set("Asia/Kathmandu");
$CurrentTime = time();
$DateTime = strftime("%Y-%m-%d %H:%M:%S", $CurrentTime);
$onlytime = strftime("%Y-%m-%d", $CurrentTime);

// Get all users for the dropdown
$user_search = "SELECT * FROM users";
$User_query = mysqli_query($conn, $user_search);

// Get readings if a user is selected
$readings = [];
if(isset($_GET['user_name']) && $_GET['user_name'] != '0') {
    $selected_user = $_GET['user_name'];
    $reading_search = "SELECT * FROM readings WHERE read_user = '$selected_user' ORDER BY read_date DESC";
    $Reading_query = mysqli_query($conn, $reading_search);
    while($row = mysqli_fetch_assoc($Reading_query)) {
        $readings[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Meter Readings Dashboard</title>
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
    }
    
    .navbar {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .profile-img {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid white;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      cursor: pointer;
      transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .profile-img:hover {
      transform: scale(1.05);
      box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }
    
    .reading-card {
      transition: all 0.3s ease;
      margin-bottom: 0px;
      border-radius: 12px;
      border: none;
      overflow: hidden;
      background: white;
      box-shadow: 0 6px 15px rgba(0,0,0,0.08);
    }
    
    .reading-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 20px rgba(0,0,0,0.12);
    }
    
    .card-header {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 15px;
      border-bottom: none;
    }
    
    .select-card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      background: white;
    }
    
    .select-card .card-body {
      padding: 25px;
    }
    
    .form-select {
      border-radius: 8px;
      padding: 10px 15px;
      border: 1px solid #e0e0e0;
    }
    
    .form-select:focus {
      border-color: var(--accent-color);
      box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
    }
    
    /* Modal for image viewing */
    .modal-img {
      max-width: 100%;
      max-height: 80vh;
      border-radius: 8px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .reading-card {
        margin-bottom: 15px;
      }
      
      .profile-img {
        width: 60px;
        height: 60px;
      }
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <i class="fas fa-tachometer-alt me-2"></i>
        <span>Meter Readings</span>
      </a>
      <div class="d-flex align-items-center">
        <span class="badge bg-light text-dark me-2">
          <i class="fas fa-calendar-day me-1"></i>
          <?php echo $onlytime; ?>
        </span>
      </div>
    </div>
  </nav>

  <div class="container">
    <!-- Header -->
    <div class="row mb-4">
      <div class="col-12">
        <h1 class="fw-bold mb-3 text-gradient">Meter Reading History</h1>
      </div>
    </div>
    
    <!-- User Selection Card -->
    <div class="row mb-5">
      <div class="col-lg-6">
        <div class="select-card">
          <div class="card-body">
            <form method="GET" action="">
              <div class="row">
                <div class="col-12">
                  <label for="user_name" class="form-label fw-semibold mb-3">Select User to View Readings</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light">
                      <i class="fas fa-user text-primary"></i>
                    </span>
                    <select class="form-select" id="user_name" name="user_name" onchange="this.form.submit()">
                      <option value="0">--- Select User ---</option>
                      <?php 
                      // Reset pointer to loop through users again
                      mysqli_data_seek($User_query, 0);
                      while($user = mysqli_fetch_assoc($User_query)): ?>
                        <option value="<?php echo $user['user_name']; ?>" 
                          <?php if(isset($selected_user) && $selected_user == $user['user_name']) echo 'selected'; ?>>
                          <?php echo $user['user_name']; ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Readings Grid -->
    <div class="row">
      <?php if(!empty($readings)): ?>
        <?php foreach($readings as $reading): ?>
          <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
            <div class="reading-card h-90">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?php echo $reading['read_month']; ?></h5>
                <span class="badge bg-white text-primary">
                  <?php echo date('M d, Y', strtotime($reading['read_date'])); ?>
                </span>
              </div>
              <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                  <img src="<?php echo $reading['read_file']; ?>" 
                       class="profile-img me-3" 
                       alt="User Image"
                       data-bs-toggle="modal" 
                       data-bs-target="#imageModal"
                       data-bs-image="<?php echo $reading['read_file']; ?>">
                  <div>
                    <h4 class="mb-0"><?php echo $reading['read_user']; ?></h4>
                    <small class="text-muted">Click image to view it larger</small>
                  </div>
                </div>
                
                <div class="row g-3">
                  <div class="col-6">
                    <div class="p-3 bg-light rounded">
                      <small class="text-muted d-block">Reading</small>
                      <span class="fw-bold"><?php echo $reading['read_value']; ?></span>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="p-3 bg-light rounded">
                      <small class="text-muted d-block">Consumed</small>
                      <span class="fw-bold"><?php echo $reading['read_cons']; ?> units</span>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="p-3 bg-primary text-white rounded">
                      <small class="d-block">Amount</small>
                      <span class="fw-bold ">Rs. <?php echo $reading['read_amount']; ?></span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php elseif(isset($selected_user)): ?>
        <div class="col-12">
          <div class="alert alert-info d-flex align-items-center">
            <i class="fas fa-info-circle me-2"></i>
            No readings found for this user.
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Image Modal -->
  <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Meter Reading Image</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <img src="" class="modal-img" id="modalImage" alt="Enlarged Meter Reading">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Initialize image modal
    const imageModal = document.getElementById('imageModal');
    if (imageModal) {
      imageModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget; // Button that triggered the modal
        const imageUrl = button.getAttribute('data-bs-image');
        const modalImage = imageModal.querySelector('#modalImage');
        modalImage.src = imageUrl;
      });
    }
  </script>
</body>
</html>