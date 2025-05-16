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
// جلب معلومات الشيف
$stmt_chef = $pdo->prepare("SELECT chefs.*, categories.name AS category_name , governorates.name AS governorate_name
                            FROM chefs 
                            JOIN categories ON chefs.category_id = categories.id 
                            JOIN governorates ON chefs.governorate_id = governorates.id
                            WHERE chefs.id = ?");
$stmt_chef->execute([$chef_id]);
$chef = $stmt_chef->fetch();

// جلب الوجبات الخاصة بالشيف
$stmt_meals = $pdo->prepare("SELECT * FROM meals WHERE chef_id = ?");
$stmt_meals->execute([$chef_id]);
$meals = $stmt_meals->fetchAll();

// استرجاع التقييمات الخاصة بالأطباق المرتبطة بالشيف
$meal_ids = array_column($meals, 'id');
if (!empty($meal_ids)) {
    $placeholders = implode(',', array_fill(0, count($meal_ids), '?'));  // التحضير لاستعلام متعدد القيم
    $stmt_reviews = $pdo->prepare("SELECT * FROM reviews WHERE meal_id IN ($placeholders)");
    $stmt_reviews->execute($meal_ids);
    $reviews = $stmt_reviews->fetchAll();
} else {
    $reviews = [];
}



// استرجاع الإشعارات من قاعدة البيانات بناءً على chef_id
$stmt_notifications = $pdo->prepare("
SELECT 
    n.id AS notification_id,
    n.message,
    n.created_at,
    n.order_id,
    u.name AS user_name,
    u.phone AS user_phone,
    m.name AS meal_name,
    om.price_after_discount AS meal_price
FROM notifications n
JOIN orders o ON o.id = n.order_id
JOIN users u ON u.id = o.user_id
JOIN order_meals om ON om.order_id = o.id
JOIN meals m ON m.id = om.meal_id
WHERE n.chef_id = ? AND n.is_read = 0
ORDER BY n.created_at DESC
");
$stmt_notifications->execute([$chef_id]);
$notifications = $stmt_notifications->fetchAll();

// معالجة قبول أو رفض الطلب من خلال الإشعارات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'], $_POST['notification_id'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status']; // accepted or rejected
    $notification_id = $_POST['notification_id'];

    // تأكد أن الشيف له علاقة بهذا الطلب عبر الإشعار
    $stmt_check = $pdo->prepare("SELECT * FROM notifications WHERE id = ? AND chef_id = ? AND order_id = ?");
    $stmt_check->execute([$notification_id, $chef_id, $order_id]);
    $notification = $stmt_check->fetch();

    if ($notification) {
        // تحديث حالة الطلب
        $stmt_update = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt_update->execute([$status, $order_id]);
    
        // جعل الإشعار مقروء (لإخفائه من العرض)
        $stmt_read = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        $stmt_read->execute([$notification_id]);
    
        // إضافة رسالة فلاش
        $_SESSION['flash_message'] = ($status == 'accepted') ? 'تم قبول الطلب بنجاح ✅' : 'تم رفض الطلب ❌';
    
        // إعادة التوجيه لتحديث الصفحة
        header("Location: chefHome.php");
        exit;
    }
    
}
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
    <style>
.dishes {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.dish {
    border: 1px solid #ddd;
    border-radius: 10px;
    overflow: hidden;
    background-color: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.dish:hover {
    transform: scale(1.02);
}

.meal-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.dish-content {
    padding: 15px;
}

.dish-content h3 {
    margin-top: 0;
    font-size: 1.2rem;
    color: #333;
}

.dish-content p {
    margin: 5px 0;
    font-size: 1rem;
    color: #555;
}

.actions {
    margin-top: 10px;
    display: flex;
    gap: 10px;
}

.actions .btn {
    flex: 1;
}
</style>

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
     <label for="upload-photo" style="cursor: pointer;">
    <img src="/luqma/login/php/uploads/<?php 
        $profilePic = !empty($chef['profile_picture']) ? 
                     htmlspecialchars($chef['profile_picture']) : 
                     'default-profile.jpg';
        echo $profilePic;
    ?>" 
    alt="صورة الشيف" 
    width="150" 
    height="150"
    onerror="this.src='/luqma/login/php/uploads/default-profile.webp'">
    </label>


</form>


            <h2><?php echo htmlspecialchars($chef['name']); ?></h2>
            <p>التصنيف: <?php echo htmlspecialchars($chef['category_name']); ?></p>
            <p><strong>الموقع:</strong> <?php echo htmlspecialchars($chef['governorate_name']); ?></p>
            <p class="rating">⭐ <?php echo count($reviews); ?> تقييم</p>
            <div class="d-flex gap-2 flex-wrap">
                <button onclick="window.location.href='/luqma/login/PHP/chef_account/chef_account.php'" class="btn btn-success">تعديل الملف الشخصي</button>
                <button onclick="window.location.href='/luqma/add_meal.php'" class="btn btn-success">أضف طبق جديد</button>


            </div>
        </div>
    </div>

    <h2 class="section-title" id="dishes">الأطباق التي أقدمها</h2>
<div class="dishes">
    <?php foreach ($meals as $meal): ?>
        <div class="dish">
        <img src="assets/img/<?php echo htmlspecialchars($meal['image']); ?>" alt="<?php echo htmlspecialchars($meal['name']); ?>" class="meal-img">
        

            <div class="dish-content">
                <h3><?php echo htmlspecialchars($meal['name']); ?></h3>

                <!-- عرض السعر والخصم إن وجد -->
                <?php if ($meal['discount'] > 0): ?>
    <p>
        <del><?php echo $meal['price']; ?>JD </del>
        <strong style="color: green;">
            <?php echo number_format($meal['price'] * (1 - $meal['discount'] / 100), 2); ?> JD
        </strong>
    </p>
<?php else: ?>
    <p><?php echo $meal['price']; ?>JD </p>
<?php endif; ?>


                <div class="actions">
                    <a href="edit_meal.php?id=<?php echo $meal['id']; ?>" class="btn btn-warning">تعديل</a>
                    <a href="delete_meal.php?id=<?php echo $meal['id']; ?>" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟');">حذف</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>


       <!-- عرض الإشعارات في صفحة الشيف -->
       
       <h2 class="section-title mt-5" id="notifications">الإشعارات</h2>
       <?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-success">
        <?php echo $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
    </div>
<?php endif; ?>

<?php if (count($notifications) > 0): ?>
    <?php foreach ($notifications as $notification): ?>
        <div class="p-3 mb-4 rounded" role="alert" style="background-color: #f2f2f2; color: #333; border: 1px solid #ddd; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
            
            <!-- نص الإشعار -->
            <p class="mb-3" style="font-weight: bold; font-size: 1.1rem;">
                <?php echo htmlspecialchars($notification['message'] ?? 'لا يوجد رسالة'); ?>
            </p>

            <!-- معلومات المستخدم -->
            <div class="d-flex flex-wrap justify-content-between mb-3" style="font-size: 0.95rem; gap: 10px;">
                <div><strong>اسم المستخدم:</strong> <span class="text-secondary"><?php echo htmlspecialchars($notification['user_name']); ?></span></div>
                <div><strong>رقم الهاتف:</strong> <span class="text-secondary"><?php echo htmlspecialchars($notification['user_phone']); ?></span></div>
                <div><strong>اسم الوجبة:</strong> <span class="text-secondary"><?php echo htmlspecialchars($notification['meal_name']); ?></span></div>
                <div><strong>السعر:</strong> <span class="text-secondary"><?php echo htmlspecialchars($notification['meal_price']); ?> د.أ</span></div>
            </div>

            <!-- تاريخ الإرسال -->
            <small class="text-muted d-block mb-3" style="font-size: 0.85rem;">
                تم الإرسال في: <?php echo htmlspecialchars($notification['created_at'] ?? ''); ?>
            </small>

            <!-- أزرار التحكم -->
            <?php if (!empty($notification['order_id'])): ?>
                <form method="post" class="d-flex gap-3 flex-wrap">
                    <input type="hidden" name="order_id" value="<?php echo $notification['order_id']; ?>">
                    <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                    <button type="submit" name="status" value="accepted" class="btn btn-success btn-sm px-3">قبول</button>
<button type="submit" name="status" value="rejected" class="btn btn-danger btn-sm px-3">رفض</button>

                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="alert alert-warning" role="alert">
        لا توجد إشعارات جديدة.
    </div>
<?php endif; ?>

</div>

    <footer id="footer" class="footer dark-background">
        <div class="container">
            <div class="row gy-3">
                <div class="col-lg-3 col-md-6 d-flex">
                    <i class="bi bi-geo-alt icon"></i>
                    <div class="address">
                        <h4>العنوان</h4>
                        <p>الاردن</p>
                        <p>عمان</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 d-flex">
                    <i class="bi bi-telephone icon"></i>
                    <div>
                        <h4>لتواصل</h4>
                        <p>
                            <strong>الهاتف:</strong> <span>+1 5589 55488 55</span><br>
                            <strong>الايميل:</strong> <span>info@example.com</span><br>
                        </p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4>تواصل معنا</h4>
                    <div class="social-links d-flex">
                        <a href="#" class="twitter"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container copyright text-center mt-4">
            <p>© <span>Copyright</span> <strong class="px-1 sitename">لقمه شهيّة</strong> <span>All Rights Reserved</span></p>
            <div class="credits">
                Designed by <a href="[#]">groub 3</a>
            </div>
        </div>
    </footer>

    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper('.reviews-slider', {
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
        });
        
    </script>
</body>
</html>
