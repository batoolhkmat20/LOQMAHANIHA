<?php
require 'db.php';
session_start();

// جلب بيانات الجلسة
$user_id = $_SESSION["user_id"] ?? null;
$user_name = $_SESSION["user_name"] ?? null;
$role = $_SESSION["role"] ?? null;

// فقط عرض رسالة ترحيب إذا كان المستخدم مسجلاً دخوله
if ($user_name) {
  //  echo "أهلاً بك، $user_name! (رقم المستخدم: $user_id الدور: $role)";
}

// جلب وجبات عليها خصومات
$stmt = $pdo->query("SELECT meals.*, chefs.name AS chef_name FROM meals 
                     JOIN chefs ON meals.chef_id = chefs.id 
                     WHERE discount > 0 
                     ORDER BY discount DESC 
                     LIMIT 6");
$ads = $stmt->fetchAll();

// وجبة عشوائية
$stmt = $pdo->prepare("SELECT id, name, chef_id, description, image FROM meals ORDER BY RAND() LIMIT 1");
$stmt->execute();
$meal = $stmt->fetch(PDO::FETCH_ASSOC);

// جلب معلومات الطاهي
$chef_stmt = $pdo->prepare("SELECT name, profile_picture FROM chefs WHERE id = ?");
$chef_stmt->bindParam(1, $meal['chef_id'], PDO::PARAM_INT);
$chef_stmt->execute();
$chef = $chef_stmt->fetch(PDO::FETCH_ASSOC);

// ------------------------
// معالجة نموذج الملاحظات
// ------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["message"])) {

    // إذا لم يكن المستخدم مسجلاً دخوله
    if (!$user_id) {
        $error = "يجب تسجيل الدخول أولاً لإرسال ملاحظة.";
    } else {
        $name = trim($_POST["name"]);
        $email = trim($_POST["email"]);
        $phone = trim($_POST["phone"]);
        $message = trim($_POST["message"]);

        try {
            $stmt = $pdo->prepare("INSERT INTO complaints (id, name, email, phone, message) 
                                   VALUES (:id, :name, :email, :phone, :message)");
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':message', $message);

            if ($stmt->execute()) {
                $_SESSION["success_message"] = "تم إرسال الملاحظة بنجاح.";
                header("Location: " . $_SERVER["PHP_SELF"]);
                exit();
            } else {
                $error = "حدث خطأ أثناء إرسال الملاحظة.";
            }
        } catch (PDOException $e) {
            $error = "خطأ في قاعدة البيانات: " . $e->getMessage();
        }
    }
}
?>




<!DOCTYPE html>


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
<!-- Bootstrap CSS -->
<link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

<!-- AOS (Animate On Scroll) CSS -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  
</head>

<body class="index-page">
  
</style>
<body class="index-page">
  <header class="header">
    <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container d-flex align-items-center justify-content-between">
         <nav id="navmenu" class="navmenu mx-auto" style="direction: rtl;   padding-left:15%;">


            <ul class="d-flex align-items-center gap-4">

                <li><a href="#footer" class="active" style="color: black ; font-weight: bold;">تواصل معنا</a></li>
                <li><a href="/LUQMA/chefAndMeal/chefs.php" style="color: black; font-weight: bold;">الطهاه</a></li>

                <li><a href="/LUQMA/index.php" class="active" style="color: black; font-weight: bold;">الرئيسيه </a></li>

                    
            </ul>
        </nav>
       <a href="/LUQMA/index.php" class="logo d-flex align-items-center" style="text-decoration: none; color: inherit;">
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

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section light-background">
  <div class="container">
    <div class="row gy-4 justify-content-center justify-content-lg-between align-items-center">
      
      <!-- النص مع العنوان والوصف -->
      <div class="col-lg-5 order-2 order-lg-1 d-flex flex-column justify-content-center">
        <h1 data-aos="fade-up">أصالة الطعم… ودفا البيت</h1>
        <p data-aos="fade-up" data-aos-delay="100">
          نُقدّم لكم وجبات منزلية محضّرة بكل حب، بطعم يذكّركم بأكلات الأمهات ونكهات لا تُنسى.
        </p>
        <!-- الزرين -->
        <div class="d-flex gap-3 mt-4" data-aos="fade-up" data-aos-delay="200">
          <a href="/LUQMA/login/user.html" class="btn btn-danger">  ابدأ طلبك
          </a>
          <a href="/LUQMA/login/chef.html" class="btn btn-danger"> ابدأ الطهي</a>
        </div>
      </div>
      
      <!-- صورة الهيرو -->
      <div class="col-lg-5 order-1 order-lg-2 hero-img" data-aos="zoom-out">
        <img src="assets/img/menu/menu-item-4.png" class="img-fluid animated" alt="صورة وجبة">
      </div>
    </div>
  </div>
</section>

    <!-- About Section -->
    <section id="about" class="about section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>من نحن <br></h2>
        <p><span>تعرف علينا</span> <span class="description-title">من نحن</span></p>
      </div>

      <div class="container">

        <div class="row gy-4">
          <div class="col-lg-7" data-aos="fade-up" data-aos-delay="100">
            <img src="assets\img\chef1.jpg" class="img-fluid mb-4" alt="chef1" style="width:70%;height:60%;">
           
          </div>
          <div class="col-lg-5" data-aos="fade-up" data-aos-delay="250">
            <div class="content ps-0 ps-lg-5">
              <p class="fst-italic"  style="direction: rtl; color: #ce1212; margin-top:60px;">
              مرحبًا بكم في لقمة هنيّة

              </p>
              <p class="fst-italic" >
                <ul style="direction: rtl;">
                <li> وجهتكم الأولى لتذوق أطيب الأكلات المنزلية المُحضّرة بكل حب
                  </span></li>
                <li> <span>                  لدينا فريق شغوف بالطهي، يقدم لكم وجبات منزلية أصيلة بطعم ولا ألذ، تمامًا كما لو كانت من يد والدتك. نؤمن أن الأكل مش بس وجبة، هو ذكريات، دفء، ولمّة حلوة.
                </span></li>
              </ul>
              <p style="direction: rtl;">
                في لقمة هنيّة نحرص على استخدام مكونات طازجة ومحلية 100%، ونُعدّ كل طبق بعناية ونظافة عالية لنضمن لكم تجربة لذيذة وآمنة.
                  
                  سواء كنت مشغولاً وما عندك وقت تطبخ، أو مشتاق لطعم بيتك... إحنا هون لخدمتك بكل حب.
                  
                  
              </p>

              <div class="position-relative mt-4">
              </div>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /About Section -->

   

    <!-- Stats Section -->
    <?php include 'stats.php'; ?>

<section id="stats" class="stats section dark-background">

  <img src="assets/img/bakground.jpg" alt="" data-aos="fade-in">

  <div class="container position-relative" data-aos="fade-up" data-aos-delay="100">

    <div class="row gy-4">

      <!-- عدد الطلبات -->
      <div class="col-lg-4 col-md-6">
        <div class="stats-item text-center w-100 h-100">
          <span data-purecounter-start="0" data-purecounter-end="<?= $orderCount ?>" data-purecounter-duration="1" class="purecounter"></span>
          <p>الطلبات</p>
        </div>
      </div>

      <!-- عدد الأطباق -->
      <div class="col-lg-4 col-md-6">
        <div class="stats-item text-center w-100 h-100">
          <span data-purecounter-start="0" data-purecounter-end="<?= $mealCount ?>" data-purecounter-duration="1" class="purecounter"></span>
          <p>الأطباق</p>
        </div>
      </div>

      <!-- عدد الطهاة -->
      <div class="col-lg-4 col-md-6">
        <div class="stats-item text-center w-100 h-100">
          <span data-purecounter-start="0" data-purecounter-end="<?= $chefCount ?>" data-purecounter-duration="1" class="purecounter"></span>
          <p>الطهاة</p>
        </div>
      </div>

    </div>

  </div>
</section>

<!-- Ads Section -->
<!-- Meal Ad Section -->
 <style>
  .carousel-control-prev-icon,
.carousel-control-next-icon {
  background-image: none; /* إلغاء السهم الافتراضي */
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  background-color: var(--bs-danger); /* استخدام اللون الأحمر الافتراضي من Bootstrap */
  border-radius: 50%;
  font-size: 1.5rem;
  color: white;
}

.carousel-control-prev-icon::after {
  content: '\2039'; /* سهم لليسار */
}

.carousel-control-next-icon::after {
  content: '\203A'; /* سهم لليمين */
}

  </style>
 <br><br>
 <section class="container my-5">
  <h2 class="text-center mb-4" style="color: #ce1212;">🍽️ عروض مميزة من شيفاتنا</h2>

  <div id="discountCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
      <?php foreach ($ads as $index => $ad): ?>
        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
          <div class="d-flex justify-content-center">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden position-relative" style="width: 22rem;">
              <?php if (!empty($ad['image'])): ?>
  <a href="chefAndMeal/getmeal.php?chef_id=<?php echo $ad['chef_id']; ?>">
    <img src="assets/img/<?php echo htmlspecialchars($ad['image']); ?>" class="card-img-top" style="height: 220px; object-fit: cover; cursor: pointer;" alt="صورة الطبق">
  </a>

</a>
              <?php endif; ?>

              <!-- شارة الخصم -->
              <div class="position-absolute top-0 end-0 bg-success text-white px-3 py-1 rounded-start rounded-bottom">
                خصم <?php echo $ad['discount']; ?>%
              </div>

              <div class="card-body bg-light">
                <h5 class="card-title text-primary"><?php echo htmlspecialchars($ad['name']); ?></h5>
                <p class="card-text text-secondary"><?php echo htmlspecialchars($ad['description']); ?></p>

                <p class="mb-1"><strong class="text-muted">السعر قبل الخصم:</strong> 
                  <s class="text-danger"><?php echo $ad['price']; ?> د.أ</s></p>
                <p class="mb-1"><strong class="text-muted">السعر بعد الخصم:</strong>
                  <span class="text-success fw-bold">
                    <?php
                      $newPrice = $ad['price'] - ($ad['price'] * $ad['discount'] / 100);
                      echo number_format($newPrice, 2);
                    ?> د.أ
                  </span>
                </p>
                <p class="text-muted small mt-2">👨‍🍳 شيف: <?php echo htmlspecialchars($ad['chef_name']); ?></p>

              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- أزرار التنقل -->
    <button class="carousel-control-prev" type="button" data-bs-target="#discountCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#discountCarousel" data-bs-slide="next" >
      <span class="carousel-control-next-icon"></span>
    </button>
  </div>
</section>

        <div class="swiper init-swiper">
         <!-- Add Swiper's CSS and JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css">
<script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>

<!-- قسم اقتراح وجبة عشوائية -->
<section id="random-meal" class="py-5" style="background-color: #f9f9f9;">
  <div class="container" data-aos="fade-up">
    <!-- العنوان -->
    <div class="text-center mb-5">
      <h2 class="fw-bold" style="color: #ce1212;">🍽️ وجبتك العشوائية لهذا اليوم</h2>
      <p class="text-muted">هل ترغب في تجربة شيء جديد؟ اخترنا لك وجبة مميزة عشوائيًا 👇</p>
    </div>

    <!-- محتوى الوجبة -->
    <div class="row justify-content-center">
      <div class="col-md-4 col-lg-4">
        <div class="card shadow-lg border-0 rounded-4 overflow-hidden text-center">
    <?php
            echo '<img src="/LUQMA/assets/img/' . htmlspecialchars($meal['image']) . '" alt="' . htmlspecialchars($meal['name']) . '" class="img-fluid" style="max-height: 300px; object-fit: cover;">';
    ?>
    <div class="card-body bg-white p-4">
        <h3 class="card-title text-danger fw-bold"><?php echo htmlspecialchars($meal['name']); ?></h3>
        <p class="card-text text-muted"><?php echo htmlspecialchars($meal['description']); ?></p>

        <a href="chefAndMeal/getmeal.php?chef_id=<?php echo $meal['chef_id']; ?>" 
           class="btn btn-danger mt-3 px-4 py-2 rounded-pill">
           شاهد وجبات هذا الطاهي
        </a>
    </div>
</div>

        </div>
      </div>
    </div>
  </div>
</section>
<section id="book-a-table" class="book-a-table section">
    <div class="container section-title" data-aos="fade-up">
        <h2>ملاحظات</h2>
        <p><span>أرسل </span> <span class="description-title">ملاحظاتك لنا<br></span></p>
    </div><!-- End Section Title -->
<div class="container py-5">
    <div class="row align-items-start g-4" data-aos="fade-up" data-aos-delay="100">
        <!-- الصورة -->
        <div class="col-lg-4">
            <div class="reservation-img" style="
                background-image: url('assets/img/ملاحظات.jpg'); 
                height: 100%;
                min-height: 400px;
                background-size: cover; 
                background-position: center; 
                border-radius: 8px;
                margin-right: 15px; /* مسافة من الجهة اليمنى */
            "></div>
        </div>

        <!-- نموذج الملاحظات -->
        <div class="col-lg-8">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" role="form" class="p-4 rounded bg-light shadow">
                <div class="row gy-3">
                    <div class="col-md-4">
                        <input type="text" name="name" class="form-control" placeholder="اسمك" required>
                    </div>
                    <div class="col-md-4">
                        <input type="email" class="form-control" name="email" placeholder="الإيميل" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="phone" placeholder="رقم الهاتف" required>
                    </div>
                </div>

                <div class="form-group mt-4">
                    <textarea class="form-control" name="message" rows="5" placeholder="اكتب ملاحظتك هنا" required></textarea>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-danger px-4 py-2">إرسال</button>
                </div>

                <!-- رسائل النجاح أو الخطأ -->
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success text-center mt-4"><?php echo $success; ?></div>
                <?php elseif (!empty($error)): ?>
                    <div class="alert alert-danger text-center mt-4"><?php echo $error; ?></div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

          <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="mb-5">
          <iframe style="width: 100%; height: 400px;" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2818359.1005945535!2d35.223194499999996!3d31.2456019!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x15006f45e59f1725%3A0x4c23b5a95fae3efc!2z2YXYs9in2YUg2KfZhNmF2YbYp9mG!5e0!3m2!1sar!2sjo!4v1713173193654!5m2!1sar!2sjo" frameborder="0" allowfullscreen=""></iframe>
        </div><!-- End Google Maps -->
        <div class="row gy-4">
          <div class="col-md-6">
            <!-- الهاتف -->
            <div class="info-item d-flex align-items-center justify-content-end text-end" data-aos="fade-up" data-aos-delay="300">
              <div>
                <h3>الهاتف</h3>
                <p>+1 5589 55488 55</p>
              </div>
              <i class="icon bi bi-telephone flex-shrink-0 me-3"></i>

            </div>
          </div>
        
          <div class="col-md-6">
            <!-- الإيميل -->
            <div class="info-item d-flex align-items-center justify-content-end text-end" data-aos="fade-up" data-aos-delay="400">
              <div>
                <h3>الإيميل</h3>
                <p>info@example.com</p>
              </div>
              <i class="icon bi bi-envelope flex-shrink-0 me-3"></i>

            </div>
          </div>
        </div>
        

    </section><!-- /Contact Section -->

  </main>

  <footer id="footer" class="footer dark-background" style="direction: rtl;">

    <div class="container">
      <div class="row gy-3">
        <div class="col-lg-3 col-md-6 d-flex">
          <i class="bi bi-geo-alt icon"></i>
          <div class="address">
            <h4>العنوان</h4>
            <p>الاردن  </p>
            <p> عمان </p>
            <p></p>
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
          <h4> تواصل معنا</h4>
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
        <!-- All the links in the footer should remain intact. -->
        <!-- You can delete the links only if you've purchased the pro version. -->
        <!-- Licensing information: https://bootstrapmade.com/license/ -->
        <!-- Purchase the pro version with working PHP/AJAX contact form: [buy-url] -->
        Designed by <a href="[#]">groub 3</a>
      </div>
    </div>

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

  <!-- Main JS File -->
  
  
  <script src="assets/js/main.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/purecounterjs@1.5.0/dist/purecounter_vanilla.js"></script>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- AOS JS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init(); // تفعيل الأنيميشن
</script>
</body>


</html>