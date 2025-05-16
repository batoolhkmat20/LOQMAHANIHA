<?php
// Database connection
$host = 'localhost';
$db = 'luqma';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$ordersResult = $conn->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5");
$customersResult = $conn->query("SELECT * FROM users ORDER BY id DESC LIMIT 3");
$chiefsResult = $conn->query("SELECT * FROM chefs ORDER BY id DESC LIMIT 3");

$allorders = $conn->query("SELECT * FROM orders ORDER BY id DESC");
$allcustomers = $conn->query("SELECT * FROM users ORDER BY id DESC");
$allchiefs = $conn->query("SELECT * FROM chefs ORDER BY id DESC");
$allratings = $conn->query("SELECT * FROM reviews ORDER BY id DESC");
$allcomplaints = $conn->query("SELECT 
    complaints.complaint_id, 
    users.id AS user_id, 
    users.name AS customer_name, 
    complaints.text
  FROM complaints 
  JOIN users ON complaints.id = users.id 
  ORDER BY complaints.complaint_id DESC");

if (!$allcomplaints) {
    die("Error in query: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Luqma Dashboard</title>
  <style>
    .styled-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      font-size: 16px;
      box-shadow: 0 0 10px rgba(78, 6, 6, 0.1);
    }
    .styled-table thead {
      background-color: #7D0A0A;
      color: #fff;
      text-align: left;
    }
    .styled-table th, .styled-table td {
      padding: 12px 15px;
      border-bottom: 1px solid #ddd556;
    }
    .styled-table tbody tr:hover {
      background-color: #f1f1f1;
    }
  </style>
  <link rel="stylesheet" href="dashboardCSS.css">
</head>
<body>
  <div class="sidebar">
    <h2>Luqma Admin Dashboard</h2>
    <ul>
      <li data-section="overview" class="active">Overview</li>
      <li data-section="customers">Customers</li>
      <li data-section="chiefs">Chiefs</li>
      <li data-section="orders">Orders</li>
      <li data-section="ratings">Ratings</li>
      <li data-section="complaints">Complaints</li>
    </ul>
  </div>

  <div class="main-content">
    <!-- Overview Section -->
    <div id="overview" class="section active">
      <h1>Overview</h1>
      <div class="overview-block">
        <h2>Last 5 Orders</h2>
        <div class="card-container">
          <?php while ($row = $ordersResult->fetch_assoc()) { ?>
            <div class="card">
              <h4>Order #<?= $row['id']; ?></h4>
              <p><strong>Order Number:</strong> <?= $row['id']; ?></p>
              <p><strong>Customer:</strong> <?= $row['user_id']; ?></p>
              <p><strong>Date:</strong> <?= $row['order_date']; ?></p>
            </div>
          <?php } ?>
        </div>
      </div>
      <div class="overview-block">
        <h2>Last 3 Customers</h2>
        <div class="card-container">
          <?php while ($row = $customersResult->fetch_assoc()) { ?>
            <div class="card">
              <h4><?= $row['name']; ?></h4>
              <p><strong>Email:</strong> <?= $row['email']; ?></p>
              <p><strong>Phone:</strong> <?= $row['phone']; ?></p>
            </div>
          <?php } ?>
        </div>
      </div>
      <div class="overview-block">
        <h2>Last 3 Chiefs</h2>
        <div class="card-container">
          <?php while ($row = $chiefsResult->fetch_assoc()) { ?>
            <div class="card">
              <h4><?= $row['name']; ?></h4>
              <p><strong>Email:</strong> <?= $row['email']; ?></p>
              <p><strong>Phone:</strong> <?= $row['phone']; ?></p>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>

    <!-- Customers Section -->
    <div id="customers" class="section">
      <table class="styled-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $allcustomers->fetch_assoc()) { ?>
            <tr>
              <td><?= $row['id']; ?></td>
              <td><?= $row['name']; ?></td>
              <td><?= $row['email']; ?></td>
              <td><?= $row['phone']; ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>

    <!-- Chiefs Section -->
    <div id="chiefs" class="section">
      <table class="styled-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $allchiefs->fetch_assoc()) { ?>
            <tr>
              <td><?= $row['id']; ?></td>
              <td><?= $row['name']; ?></td>
              <td><?= $row['email']; ?></td>
              <td><?= $row['phone']; ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>

    <!-- Orders Section -->
    <div id="orders" class="section">
      <h1>Orders</h1>
      <table class="styled-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Customer ID</th>
            <th>Order Date</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $allorders->fetch_assoc()) { ?>
            <tr>
              <td><?= $row['id']; ?></td>
              <td><?= $row['user_id']; ?></td>
              <td><?= $row['order_date']; ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>

    <!-- Ratings Section -->
    <div id="ratings" class="section">
      <h1>Ratings</h1>
      <table class="styled-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Meal ID</th>
            <th>Comment</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $allratings->fetch_assoc()) { ?>
            <tr>
              <td><?= $row['id']; ?></td>
              <td><?= $row['meal_id']; ?></td>
              <td><?= $row['comment']; ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>

    <!-- Complaints Section -->
    <div id="complaints" class="section">
  <h1>Complaints</h1>
  <table class="styled-table">
    <thead>
      <tr>
        <th>Complaint #</th>
        <th>Customer ID</th>
        <th>Customer Name</th>
        <th>Comment</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $allcomplaints->fetch_assoc()) { ?>
        <tr>
          <td><?php echo $row['complaint_id']; ?></td>
          <td><?php echo $row['user_id']; ?></td>
          <td><?php echo $row['customer_name']; ?></td>
          <td><?php echo $row['text']; ?></td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<script src="dashboardJS.js"></script>
</body>
</html>