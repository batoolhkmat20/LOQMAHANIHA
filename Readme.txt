Thanks for downloading this template!

Template Name: Yummy
Template URL: https://bootstrapmade.com/yummy-bootstrap-restaurant-website-template/
Author: BootstrapMade.com
License: https://bootstrapmade.com/license/





<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

session_start();

// Redirect if not logged in
if (!isset($_SESSION["chef_id"])) {
    header("Location: ../login/chef.html");
    exit;
}

// Use the session chef_id
$chef_id = $_SESSION["chef_id"];

// Fetch chef information
$stmt_chef = $pdo->prepare("SELECT chefs.*, categories.name AS category_name 
                          FROM chefs 
                          JOIN categories ON chefs.category_id = categories.id 
                          WHERE chefs.id = ?");
$stmt_chef->execute([$chef_id]);
$chef = $stmt_chef->fetch();

// Handle case when chef not found
if (!$chef) {
    die("لا يمكن العثور على بيانات الشيف. الرجاء التأكد من تسجيل الدخول بشكل صحيح.");
}

// Handle photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'assets/img/chefs/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $photoName = uniqid() . '_' . basename($_FILES['photo']['name']);
    $uploadPath = $uploadDir . $photoName;

    // Validate image
    $check = getimagesize($_FILES['photo']['tmp_name']);
    if ($check === false) {
        die("الملف المرفوع ليس صورة.");
    }

    // Delete old photo if exists
    if (!empty($chef['photo']) && file_exists($uploadDir . $chef['photo'])) {
        unlink($uploadDir . $chef['photo']);
    }

    // Move new photo
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
        // Update database
        $stmt_update_photo = $pdo->prepare("UPDATE chefs SET photo = ? WHERE id = ?");
        $stmt_update_photo->execute([$photoName, $chef_id]);
        
        // Refresh to show new photo
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    } else {
        die("حدث خطأ أثناء رفع الصورة.");
    }
}

// Fetch chef's meals
$stmt_meals = $pdo->prepare("SELECT * FROM meals WHERE chef_id = ?");
$stmt_meals->execute([$chef_id]);
$meals = $stmt_meals->fetchAll();

// Fetch reviews for chef's meals
$meal_ids = array_column($meals, 'id');
$reviews = [];
if (!empty($meal_ids)) {
    $placeholders = implode(',', array_fill(0, count($meal_ids), '?'));
    $stmt_reviews = $pdo->prepare("SELECT * FROM reviews WHERE meal_id IN ($placeholders)");
    $stmt_reviews->execute($meal_ids);
    $reviews = $stmt_reviews->fetchAll();
}

// Fetch notifications
$stmt_notifications = $pdo->prepare("SELECT * FROM notifications WHERE chef_id = ? ORDER BY created_at DESC");
$stmt_notifications->execute([$chef_id]);
$notifications = $stmt_notifications->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>لقمه هنيّة</title>
    <meta name="description" content="">
    <meta name="keywords" content="">
  
    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
  
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
  
    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  
    <!-- Main CSS File -->
    <link href="assets/css/main.css" rel="stylesheet">
</head>
<body class="body-chef">
    <header id="header" class="header d-flex align-items-center sticky-top">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="index.html" class="logo d-flex align-items-center">
                <h1 class="sitename">لقمة هنيّة</h1>
            </a>
            <nav id="navmenu" class="navmenu mx-auto" style="margin-top: -25px;">
                <ul class="d-flex align-items-center gap-4">
                    <li><a href="#dishes">الأطباق</a></li><br>
                    <li><a href="#notifications">الإشعارات</a></li>
                    <li><a href="#reviews">التقييمات</a></li>
                    <li><a href="login/chef.html">تسجيل الخروج</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="profile">
            <div class="profile-info">
                <form method="post" enctype="multipart/form-data" id="photo-form">
                    <input type="file" name="photo" id="upload-photo" style="display: none;" accept="image/*" onchange="document.getElementById('photo-form').submit();">
                    <label for="upload-photo" style="cursor: pointer;">
                        <img src="assets/img/chefs/<?php echo !empty($chef['profile_picture']) ? htmlspecialchars($chef['profile_picture']) : 'default.jpg'; ?>" 
                             alt="صورة الشيف" 
                             class="profile-img" 
                             title="اضغط لتغيير الصورة">
                    </label>
                </form>