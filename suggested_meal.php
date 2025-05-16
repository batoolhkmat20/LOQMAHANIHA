<?php
session_start();

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'luqma';

$conn = new mysqli($host, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// التحقق من وجود معرف الشيف
$chefId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($chefId === 0) {
    echo "معرف الشيف غير صالح";
    exit;
}

// جلب بيانات الشيف
$chefQuery = "SELECT chefs.*, governorates.name AS governorate_name 
              FROM chefs 
              JOIN governorates ON chefs.governorate_id = governorates.id 
              WHERE chefs.id = ?";
$stmt = $conn->prepare($chefQuery);
$stmt->bind_param("i", $chefId);
$stmt->execute();
$chefResult = $stmt->get_result();

if ($chefResult->num_rows === 0) {
    echo "الشيف غير موجود.";
    exit;
}
$chef = $chefResult->fetch_assoc();

// جلب وجبات الشيف
$mealsQuery = "SELECT * FROM meals WHERE chef_id = ?";
$stmt = $conn->prepare($mealsQuery);
$stmt->bind_param("i", $chefId);
$stmt->execute();
$mealsResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>ملف الشيف - <?php echo htmlspecialchars($chef['name']); ?></title>
    <link href="/LUQMA/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .chef-card { margin-top: 30px; }
        .meal-card { margin-bottom: 20px; }
        .meal-img { width: 100%; height: 200px; object-fit: cover; }
    </style>
</head>
<body class="container py-5">

    <h2 class="text-center mb-4">ملف الشيف: <?php echo htmlspecialchars($chef['name']); ?></h2>

    <div class="card chef-card p-4 shadow-sm">
        <h4>الاسم: <?php echo htmlspecialchars($chef['name']); ?></h4>
        <p>رقم الهاتف: <?php echo htmlspecialchars($chef['phone']); ?></p>
        <p>الموقع: <?php echo htmlspecialchars($chef['governorate_name']); ?></p>
        <p>الوصف: <?php echo htmlspecialchars($chef['description']); ?></p>
    </div>

    <h3 class="mt-5">الوجبات المقدمة من الشيف</h3>
    <div class="row">
        <?php while ($meal = $mealsResult->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card meal-card shadow">
                    <img src="/LUQMA/uploads/<?php echo htmlspecialchars($meal['image']); ?>" class="meal-img card-img-top" alt="وجبة">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($meal['name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($meal['description']); ?></p>
                        <?php if ($meal['discount'] > 0): ?>
                            <p class="text-danger"><del><?php echo $meal['price']; ?> د.أ</del> → <?php echo $meal['price_after_discount']; ?> د.أ</p>
                        <?php else: ?>
                            <p><?php echo $meal['price']; ?> د.أ</p>
                        <?php endif; ?>
                        <a href="#" class="btn btn-success">اطلب الآن</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

</body>
</html>