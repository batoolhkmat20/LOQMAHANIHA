<?php
require_once 'db.php';

session_start();

// التأكد من أن الشيف مسجّل دخول
if (!isset($_SESSION['chef_id'])) {
    header("Location: login/chef.html");
    exit;
}

// استخدام معرف الشيف من الجلسة
$chef_id = $_SESSION['chef_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $discount = isset($_POST['discount']) ? intval($_POST['discount']) : 0;

    // التحقق من صحة الخصم
    if ($discount < 0 || $discount > 100) {
        $discount = 0; // يمكنك أيضًا إرسال رسالة تنبيه
    }

    // رفع الصورة
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_name = time() . '_' . basename($_FILES['image']['name']);
        $target_path = "assets/img/" . $image_name;
        move_uploaded_file($_FILES['image']['tmp_name'], $target_path);
    } else {
        $image_name = null;
    }

    // إدخال البيانات
    $stmt = $pdo->prepare("INSERT INTO meals (chef_id, name, description, price, discount, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$chef_id, $name, $description, $price, $discount, $image_name]);

    header("Location: chefHome.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إضافة طبق جديد</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h2 style="color: #ce1212;">إضافة طبق جديد</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label>اسم الطبق</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>الوصف</label>
            <textarea name="description" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label>السعر</label>
            <input type="number" name="price" class="form-control" step="0.01" required>
        </div>
        <div class="mb-3">
            <label>الخصم (%)</label>
            <input type="number" name="discount" class="form-control" min="0" max="100" value="0">
        </div>
        <div class="mb-3">
            <label>صورة الطبق</label>
            <input type="file" name="image" class="form-control">
        </div>
        <button type="submit" class="btn" style="background-color: #ce1212; color: white;">حفظ</button>
    </form>
</body>
</html>
