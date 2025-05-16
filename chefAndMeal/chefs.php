<?php
$host = 'localhost'; 
$username = 'root'; 
$password = ''; 
$dbname = 'luqma'; 

session_start();

// التحقق من وجود معلومات الجلسة، إذا كانت موجودة نستخدمها، وإذا لا، نكمل الصفحة عادي
$user_id = $_SESSION["user_id"] ?? null;
$user_name = $_SESSION["user_name"] ?? null;
$role = $_SESSION["role"] ?? null;

// فقط عرض رسالة ترحيب إذا كان المستخدم مسجلاً دخوله
if ($user_name) {
   // echo "أهلاً بك، $user_name! (رقم المستخدم: $user_id الدور: $role)";
}

// الاتصال بقاعدة البيانات
$conn = new mysqli($host, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// الحصول على قيمة القسم (إذا تم تحديده)
$categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;  // أو 1 للأكل الصحي
$governorateId = isset($_POST['governorate_id']) ? (int) $_POST['governorate_id'] : null;

// بناء الاستعلام الأساسي مع ترتيب عشوائي
$query = "SELECT chefs.*, governorates.name AS governorate_name 
          FROM chefs 
          JOIN governorates ON chefs.governorate_id = governorates.id";

// إضافة فلترة حسب القسم (إذا كان موجودًا)
if ($categoryId) {
    $query .= " WHERE chefs.category_id = $categoryId";
}

// إضافة فلترة حسب المكان (إذا كان موجودًا)
if ($governorateId) {
    $query .= $categoryId ? " AND chefs.governorate_id = $governorateId" : " WHERE chefs.governorate_id = $governorateId";
}

// إضافة ترتيب عشوائي
$query .= " ORDER BY RAND()";  

// تنفيذ الاستعلام
$result = $conn->query($query);

if (!$result) {
    die("حدث خطأ أثناء تنفيذ الاستعلام: " . $conn->error);
}

// دالة لطباعة النجوم حسب التقييم
function printStars($rating) {
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5 ? true : false;
    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

    $starsHtml = str_repeat('★', $fullStars);
    if ($halfStar) {
        $starsHtml .= '½';
    }
    $starsHtml .= str_repeat('☆', $emptyStars);

    return $starsHtml;
}
?>










<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>لقمه هنيه</title>
    <link href="/LUQMA/assets/img/favicon.png" rel="icon">
    <link href="/LUQMA/assets/img/apple-touch-icon.png" rel="apple-touch-icon">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo&display=swap" rel="stylesheet">
    <link href="/LUQMA/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/LUQMA/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="/LUQMA/assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="/LUQMA/assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="/LUQMA/assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="/LUQMA/assets/css/main.css" rel="stylesheet">
    <link href="chefs.css" rel="stylesheet">

</head>

<body class="index-page">
<header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container d-flex align-items-center justify-content-between">
            <nav id="navmenu" class="navmenu mx-auto" style="direction: rtl;   padding-left:15%;">


<ul class="d-flex align-items-center gap-4 justify-content-end">

                <li><a href="#footer" class="active" style="color: black ; font-weight: bold;">تواصل معنا</a></li>
                <li><a href="/LUQMA/chefAndMeal/chefs.php" style="color: black; font-weight: bold;">الطهاه</a></li>

                <li><a href="/LUQMA/index.php" class="active" style="color: black; font-weight: bold;">الرئيسيه </a></li>

                    
            </ul>
          
        </nav>
        <a href="/LUQMA/index.php" class="logo d-flex align-items-center">
            <h1 class="sitename">لقمة هنيّة</h1>
        </a>
    </div>
    
<!-- قائمة الحساب -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const profileButton = document.querySelector('.profile-button');
    const dropdownMenu = document.querySelector('.dropdown-menu');
    
    console.log('Profile Button:', profileButton);
    console.log('Dropdown Menu:', dropdownMenu);
    
    if (profileButton && dropdownMenu) {
      profileButton.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdownMenu.classList.toggle('show');
      });
      
      document.addEventListener('click', function() {
        dropdownMenu.classList.remove('show');
      });
      
      dropdownMenu.addEventListener('click', function(e) {
        e.stopPropagation();
      });
    } else {
      console.error('Could not find required elements!');
    }
  });
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

<div class="profile-menu-container">
    <button class="profile-button">
        <i class="fa-solid fa-user"></i>
    </button>
    <!-- قائمة الحساب (إذا كان المستخدم مسجلاً دخوله) -->
<?php if (isset($_SESSION['user_name'])): ?>
        <div class="dropdown-menu">
            <a href="/LUQMA/order/previous_orders.html"><i class="fa-solid fa-cart-shopping"></i> طلباتي السابقة</a>
            <a href="/luqma/login/php/user_account/settings_account.php"><i class="fa-solid fa-user-pen"></i> معلومات الحساب</a>
            <a href="/luqma/login/PHP/logout.php"><i class="fa-solid fa-right-from-bracket"></i> تسجيل الخروج</a>
        </div>
    <?php else: ?>
        <!-- في حالة عدم تسجيل الدخول -->
        <div class="dropdown-menu">
            <a href="/LUQMA/login/user.html"><i class="fa-solid fa-user"></i> تسجيل دخول</a>
        </div>
    <?php endif; ?>
</div>

<i class="mobile-nav-toggle d-xl-none bi bi-list"></i>

</header>
<section id="menu" class="menu section" style="margin-top:-30px;" >
  <div class="container section-title text-center" data-aos="fade-up" style="margin-top: 10px; margin-bottom: 20px; padding-left:15%;">
  <h5><span style="color:rgb(199, 5, 5);">فريقنا</span> في خدمتكم</h5>
</div>





  <div class="container" style="position: relative; top: -30px;">

    <ul class="nav nav-tabs d-flex justify-content-center" data-aos="fade-up" data-aos-delay="100">
      <li class="nav-item">
        <a class="nav-link <?php echo $categoryId == 2 ? 'active' : ''; ?>" href="chefs.php?category_id=2"><h4>حلويات</h4></a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $categoryId == 3 ? 'active' : ''; ?>" href="chefs.php?category_id=3"><h4>غداء</h4></a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $categoryId == 4 ? 'active' : ''; ?>" href="chefs.php?category_id=4"><h4>فطور</h4></a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $categoryId == 1 ? 'active' : ''; ?>" href="chefs.php?category_id=1"><h4>أكل صحي</h4></a>
      </li>
    </ul>
  </div>
<div class="container my-3 d-flex align-items-center gap-3" style="direction: rtl;">
    <form method="POST" action="" class="d-flex align-items-center" style="gap: 15px;">
        <label for="locationFilter" class="form-label fs-6 fw-semibold text-secondary mb-0" style="margin-bottom: 0;">اختر المكان:</label>
        <select id="locationFilter" class="form-select form-select-sm shadow-sm border-primary rounded-2" name="governorate_id" style="width: 150px;">
            <option value="" selected>اختر المكان</option>
            <?php
            $govResult = $conn->query("SELECT id, name FROM governorates");
            if ($govResult && $govResult->num_rows > 0) {
                while ($gov = $govResult->fetch_assoc()) {
                    $selected = isset($_POST['governorate_id']) && $_POST['governorate_id'] == $gov['id'] ? 'selected' : '';
                    echo "<option value='{$gov['id']}' $selected>{$gov['name']}</option>";
                }
            }
            ?>
        </select>
        <button class="btn btn-danger btn-sm rounded-2 shadow-sm ms-3" type="submit">عرض</button>
    </form>
</div><div class="container" id="chefsList">
  <div class="row d-flex justify-content-end">
    <?php
    while ($row = $result->fetch_assoc()) {
        $categoryId = $row['category_id'];

        // استعلام لجلب اسم القسم
        $categoryStmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
        $categoryStmt->bind_param("i", $categoryId);
        $categoryStmt->execute();
        $categoryResult = $categoryStmt->get_result();
        $categoryRow = $categoryResult->fetch_assoc();
        $categoryName = $categoryRow ? htmlspecialchars($categoryRow['name']) : "غير معروف";

        $chefId = $row['id'];

        // جلب متوسط تقييم الشيف
        $stmt = $conn->prepare("SELECT AVG(chef_rating) AS avg_rating FROM order_ratings WHERE chef_id = ?");
        $stmt->bind_param("i", $chefId);
        $stmt->execute();
        $res = $stmt->get_result();
        $ratingData = $res->fetch_assoc();
        $avgRating = $ratingData['avg_rating'] ? round($ratingData['avg_rating'], 1) : 0;

        $stars = printStars($avgRating);
        //صوره افتراضيه

        $profilePic = !empty($row['profile_picture']) ? htmlspecialchars($row['profile_picture']) : 'default-profile.jpg';

        echo "<div class='col-md-4 mb-3'>
                <a href='getmeal.php?chef_id=" . htmlspecialchars($row['id']) . "' class='text-decoration-none text-dark'>
                    <div class='chef-card d-flex border rounded p-3 shadow-sm transition' style='align-items: center; height: 100%;'>
                        <img 
                            src='/luqma/login/php/uploads/" . $profilePic . "' 
                            alt='" . htmlspecialchars($row['name']) . "' 
                            class='chef-image'
                              style='width:100px; height:100px; object-fit: cover;'

                            onerror=\"this.onerror=null; this.src='/luqma/login/php/uploads/default-profile.webp';\">
                        <div>
                            <h5 class='mb-1'>" . htmlspecialchars($row['name']) . "</h5>
                            <p class='mb-0'>" . htmlspecialchars($row['governorate_name']) . "</p>
                            <p class='mb-0 text-muted'>القسم: " . htmlspecialchars($categoryName) . "</p>
                            <h6 class='mb-1'>" . htmlspecialchars($row['phone']) . "</h6>
                            <div class='chef-rating' style='color: #ffc107; font-size: 14px;'>" . $stars . " (" . htmlspecialchars($avgRating) . ")</div>
                        </div>
                    </div>
                </a>
              </div>";
    }
    ?>
  </div>
</div>
</section>



<footer id="footer" class="footer dark-background" style="direction: rtl; margin-top: 50px;">
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
                    <p><strong>الهاتف:</strong> <span>+1 5589 55488 55</span><br><strong>الايميل:</strong> <span>info@example.com</span></p>
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
        <p>© <span>Copyright</span> <strong class="px-1 sitename">لقمة شهيّة</strong> <span>All Rights Reserved</span></p>
        <div class="credits">
            Designed by <a href="#">groub 3</a>
        </div>
    </div>
</footer>

<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
<script src="/LUQMA/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/LUQMA/assets/vendor/php-email-form/validate.js"></script>
<script src="/LUQMA/assets/vendor/aos/aos.js"></script>
<script src="/LUQMA/assets/vendor/glightbox/js/glightbox.min.js"></script>
<script src="/LUQMA/assets/vendor/purecounter/purecounter_vanilla.js"></script>
<script src="/LUQMA/assets/vendor/swiper/swiper-bundle.min.js"></script>
<script src="/LUQMA/assets/js/main.js"></script>

<?php
$conn->close();
?>
</body>
</html>
