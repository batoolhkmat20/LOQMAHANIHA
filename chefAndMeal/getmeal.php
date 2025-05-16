<?php
// بدء الجلسة



session_start();

// التحقق من وجود معلومات الجلسة، إذا كانت موجودة نستخدمها، وإذا لا، نكمل الصفحة عادي
$user_id = $_SESSION["user_id"] ?? null;
$user_name = $_SESSION["user_name"] ?? null;
$role = $_SESSION["role"] ?? null;

// فقط عرض رسالة ترحيب إذا كان المستخدم مسجلاً دخوله
if ($user_name) {
  // echo "👋 مرحبًا، $user_name! ";
}

// الاتصال بقاعدة البيانات
$conn = new mysqli('localhost', 'root', '', 'luqma');
$conn->set_charset('utf8');

if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

$priceFilter = isset($_POST['priceFilter']) ? $_POST['priceFilter'] : 'all';
$sql = "SELECT * FROM meals WHERE chef_id = ?";

if ($priceFilter !== 'all') {
    $sql .= " AND price <= ?";
}

$chef_id = isset($_GET['chef_id']) ? intval($_GET['chef_id']) : 0;
$stmt = $conn->prepare($sql);
if ($priceFilter !== 'all') {
    $stmt->bind_param("ii", $chef_id, $priceFilter); // ربط chef_id مع price
} else {
    $stmt->bind_param("i", $chef_id); // ربط chef_id فقط
}
// جلب اسم الشيف (مثال)
$chef_id = $_GET['chef_id']; // أو حسب طريقة تمريرك للمعرّف
$chef_query = "SELECT name FROM chefs WHERE id = $chef_id";
$chef_result = $conn->query($chef_query);
$chefName = "";

if ($chef_result && $chef_result->num_rows > 0) {
    $row = $chef_result->fetch_assoc();
    $chefName = $row['name'];
}
 
// دالة طباعة النجوم
function printStars($rating) {
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

    $starsHtml = str_repeat('★', $fullStars);
    if ($halfStar) {
        $starsHtml .= '½';
    }
    $starsHtml .= str_repeat('☆', $emptyStars);

    return $starsHtml;
}

$stmt->execute();
$result = $stmt->get_result();
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

    <!-- Main CSS File -->
    <link href="\LUQMA\assets\css\main.css" rel="stylesheet">
</head>

<body class="index-page">
 <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container d-flex align-items-center justify-content-between">
         <nav id="navmenu" class="navmenu mx-auto" style="direction: rtl;   padding-left:15%;">


            <ul class="d-flex align-items-center gap-4">

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
    <!-- محتوى الصفحة -->
    <div class="container my-4" dir="rtl">
        <!-- فلتر السعر -->
        <form method="POST" action="" class="d-flex justify-content-end align-items-center gap-2 mb-4">
            <label for="priceFilter" class="fw-bold mb-0">فلتر السعر:</label>
            <select id="priceFilter" class="form-select w-auto" name="priceFilter">
                <option value="all" <?php echo $priceFilter == 'all' ? 'selected' : ''; ?>>جميع الأسعار</option>
                <option value="5" <?php echo $priceFilter == '5' ? 'selected' : ''; ?>>أقل من ٥ د.أ</option>
                <option value="10" <?php echo $priceFilter == '10' ? 'selected' : ''; ?>>أقل من ١٠ د.أ</option>
                <option value="15" <?php echo $priceFilter == '15' ? 'selected' : ''; ?>>أقل من ١٥ د.أ</option>
            </select>
            <button type="submit" class="btn btn-danger btn-sm">حدد السعر</button>
        </form>

        <!-- عرض الوجبات -->
        <?php
        if ($result->num_rows > 0) {
// جلب اسم الطباخ مرة واحدة فقط
$chef_id = $_GET['chef_id'] ?? null; // أو الطريقة التي تستخدمها لجلب معرف الشيف
$chefName = "";

if ($chef_id) {
    $stmt = $conn->prepare("SELECT name FROM chefs WHERE id = ?");
    $stmt->bind_param("i", $chef_id);
    $stmt->execute();
    $resultChef = $stmt->get_result();

    if ($resultChef && $resultChef->num_rows > 0) {
        $rowChef = $resultChef->fetch_assoc();
        $chefName = $rowChef['name'];
    }

}

// طباعة اسم الطباخ مرة واحدة فقط إذا موجود
if ($chefName) {
echo '<h4 style="color: #333; margin-bottom: 20px; text-align: center;"> وجبات ' . htmlspecialchars($chefName) . '</h4>';
}

// بعدها تبدأ عرض الوجبات بنفس استعلامك العادي (يمكن فلترة الوجبات حسب $chef_id)


    while ($meal = $result->fetch_assoc()) {
                    $mealId = $meal['id'];

        // حساب التقييم
        $stmtRating = $conn->prepare("SELECT AVG(meal_rating) AS avg_rating FROM order_ratings WHERE meal_id = ?");
        $stmtRating->bind_param("i", $mealId);
        $stmtRating->execute();
        $ratingResult = $stmtRating->get_result();
        $ratingData = $ratingResult->fetch_assoc();
        $avgRating = $ratingData['avg_rating'] ? round($ratingData['avg_rating'], 1) : 0;
        $stmtRating->close();

        $image = urlencode($meal['image']);

        echo '<div class="col-9 mb-3 mx-auto">
                <div class="card shadow-sm rounded-3 p-3 meal-card" data-price="' . $meal['price'] . '" style="width: 100%; margin-bottom: 20px;">
                    <div class="row align-items-center">
                        <div class="col-md-4 mb-3 mb-md-0" style="text-align: right;">
                            <img src="/LUQMA/assets/img/' . $image . '" alt="صورة الوجبة"
                                 class="img-fluid rounded"
                                 style="max-height: 200px; object-fit: cover; float: right; margin-left: 15px;">
                        </div>
                        <div class="col-md-8">
                            <h5 class="fw-bold mb-1">اسم الوجبة: ' . htmlspecialchars($meal['name']) . '</h5>
                            <p class="text-muted mb-1">وصف: ' . htmlspecialchars($meal['description']) . '</p>
                            <p>تقييم الوجبة: <span style="color:#ffc107; font-size: 16px;">' . printStars($avgRating) . " ($avgRating)</span></p>";

        if (!empty($meal['discount']) && $meal['discount'] > 0) {
            echo '<p><del>' . number_format($meal['price'], 2) . ' د.أ</del> <strong style="color: green;">' . number_format($meal['price'] * (1 - $meal['discount'] / 100), 2) . ' د.أ</strong></p>';
        } else {
            echo '<p>' . number_format($meal['price'], 2) . ' د.أ</p>';
        }

        echo '<div class="d-flex justify-content-center align-items-center gap-2 mt-3">
        <input type="checkbox" class="form-check-input" id="meal_' . $meal['id'] . '" name="meal_id[]" value="' . $meal['id'] . '">
                <input type="hidden" name="price[]" value="' . $meal['price'] . '">
        <label for="meal_' . $meal['id'] . '" class="text-primary fw-bold" style="cursor:pointer;">أضف</label>
              </div>
            </div>  <!-- إغلاق col-md-8 -->
          </div>    <!-- إغلاق row align-items-center -->
        </div>      <!-- إغلاق card --> 
    </div>';   
    } // نهاية اللوب

    // زر "أكمل الطلب" يطبع مرة واحدة فقط خارج اللوب
    echo '<div class="text-center mt-4">
            <button type="button" onclick="saveSelectedMeals()" class="btn btn-danger">أكمل الطلب</button>
          </div>';

} else {
    echo "<div class='col-12 text-center text-muted'>لا توجد وجبات في هذه الفئة</div>";
}

 $stmt->close();
$conn->close();
?>

 </div>
  </div>
</section>
    <footer id="footer" class="footer dark-background w-100" style="direction: rtl; margin-top: 50px; padding: 20px 0;">
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

<!-- Scroll Top -->
<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<!-- Preloader -->
<div id="preloader"></div>

<!-- Vendor JS Files -->
<script src="/LUQMA/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/LUQMA/assets/vendor/php-email-form/validate.js"></script>
<script src="/LUQMA/assets/vendor/aos/aos.js"></script>
<script src="/LUQMA/assets/vendor/glightbox/js/glightbox.min.js"></script>
<script src="/LUQMA/assets/vendor/purecounter/purecounter_vanilla.js"></script>
<script src="/LUQMA/assets/vendor/swiper/swiper-bundle.min.js"></script>
<script src="/LUQMA/assets/js/main.js"></script>


<script src="assets/js/main.js"></script>

<script>
function saveSelectedMeals() {
    var selectedMeals = [];
    var checkboxes = document.querySelectorAll('input[type="checkbox"][name="meal_id[]"]:checked');

    checkboxes.forEach(function(checkbox) {
        var card = checkbox.closest('.meal-card');

        var mealId = checkbox.value;
        var price = parseFloat(card.querySelector('input[name="price[]"]').value);  // السعر النهائي بعد الخصم
        var name = card.querySelector('h5').textContent.replace('اسم الوجبة: ', '').trim();

        var discount = 0;

        var originalPriceElem = card.querySelector('.text-danger');  // السعر الأصلي
        var finalPriceElem = card.querySelector('.text-success');  // السعر النهائي بعد الخصم

        // تحقق من استخراج الأسعار بشكل صحيح
        if (originalPriceElem && finalPriceElem) {
            var originalPrice = parseFloat(originalPriceElem.textContent.replace(/[^\d.]/g, ''));  // استخراج السعر الأصلي
            var finalPrice = parseFloat(finalPriceElem.textContent.replace(/[^\d.]/g, ''));  // استخراج السعر النهائي

            // تأكد من أن السعر النهائي والخصم يتم حسابهم بشكل صحيح
            if (!isNaN(originalPrice) && !isNaN(finalPrice)) {
                discount = originalPrice - finalPrice;  // حساب الخصم
                price = finalPrice;  // استخدم السعر بعد الخصم
            }
        }

        // إضافة وجبة مع السعر الأصلي، السعر النهائي، والخصم
        selectedMeals.push({
            id: mealId,
            name: name,
            originalPrice: originalPrice,  // حفظ السعر الأصلي
            price: price,  // حفظ السعر النهائي بعد الخصم
            discount: discount  // حفظ قيمة الخصم
        });
    });

    if (selectedMeals.length > 0) {
        // حفظ البيانات في localStorage
        localStorage.setItem('selectedMeals', JSON.stringify(selectedMeals));
        window.location.href = '/LUQMA/order/order.html';  
    } else {
        alert('يرجى اختيار وجبة واحدة على الأقل قبل متابعة الطلب.');
    }
}


</script>

</body>


</html>