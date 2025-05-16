<?php
require_once 'db.php';

$meal_id = $_GET['id'] ?? null;

if (!$meal_id) {
    die("رقم الطبق غير موجود.");
}

// جلب بيانات الطبق
$stmt = $pdo->prepare("SELECT * FROM meals WHERE id = ?");
$stmt->execute([$meal_id]);
$meal = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $discount = $_POST['discount'];

    // مبدئياً الصورة القديمة
    $image = $meal['image'];

    // لو رفع صورة جديدة
    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . '_' . $_FILES['image']['name'];
        $imageTmpPath = $_FILES['image']['tmp_name'];
        $uploadPath = 'assets/img/' . $imageName;

        // رفع الصورة الجديدة
        move_uploaded_file($imageTmpPath, $uploadPath);

        // حذف الصورة القديمة إذا موجودة
        if (!empty($meal['image']) && file_exists('assets/img/' . $meal['image'])) {
            unlink('assets/img/' . $meal['image']);
        }

        // تحديث اسم الصورة الجديد
        $image = $imageName;
    }

    // تحديث بيانات الطبق
    $updateStmt = $pdo->prepare("UPDATE meals SET name = ?, description = ?, price = ?, discount = ?, image = ? WHERE id = ?");
    $updateStmt->execute([$name, $description, $price, $discount, $image, $meal_id]);

    header("Location: chefHome.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعديل الطبق</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h2 class="mb-4" style="color: #ce1212;">تعديل الطبق</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label>اسم الطبق</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($meal['name']); ?>">
        </div>
        <div class="mb-3">
            <label>الوصف</label>
            <textarea name="description" class="form-control"><?php echo htmlspecialchars($meal['description']); ?></textarea>
        </div>
        <div class="mb-3">
            <label>السعر</label>
            <input type="text" name="price" class="form-control" value="<?php echo htmlspecialchars($meal['price']); ?>">
        </div>
        <div class="mb-3">
            <label>الخصم</label>
            <input type="text" name="discount" class="form-control" value="<?php echo htmlspecialchars($meal['discount']); ?>">
        </div>

        <div class="mb-3">
            <label>صورة الطبق (اختياري)</label><br>
            <?php if (!empty($meal['image'])): ?>
                <img src="assets/img/<?php echo htmlspecialchars($meal['image']); ?>" alt="صورة الطبق" style="width: 100px; height: auto; margin-bottom: 10px;"><br>
            <?php endif; ?>
            <input type="file" name="image" class="form-control">
        </div>

        <button type="submit" class="btn" style="background-color: #ce1212; color: white;">تحديث</button>
    </form>
</body>
</html>
