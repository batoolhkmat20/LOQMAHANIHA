<?php
session_start();
if (!isset($_SESSION["chef_id"])) {
    header("Location: ../login/chef.html");
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=luqma;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fetch chef data
$stmt = $pdo->prepare("SELECT name, email, phone, profile_picture, governorate_id, category_id FROM chefs WHERE id = ?");
$stmt->execute([$_SESSION["chef_id"]]);
$chef = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$chef) {
    echo "<div class='alert alert-error'>لم يتم العثور على بيانات الحساب.</div>";
    exit;
}

// Set default profile picture if none exists
$profile_picture = !empty($chef['profile_picture']) ? $chef['profile_picture'] : 'default-profile.png';

// Handle success/error messages
$success = isset($_GET['success']) && $_GET['success'] == 1;
$error = isset($_GET['error']) ? urldecode($_GET['error']) : '';


?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>إعدادات الحساب</title>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Cairo', sans-serif; }
    body { background: linear-gradient(to right, #e2e2e2, #ffe9c9); margin: 0; padding: 0; direction:rtl; }
    .header { display: flex; justify-content: space-between; align-items: center; padding: 15px 30px; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1); direction:ltr; }
    .logo h1 { margin: 0; font-size: 24px; color: #333; }
    .logo span { color: #d32f2f; }
    .navmenu ul { list-style: none; display: flex; gap: 20px; margin: 0; padding: 0; }
    .navmenu ul li a { text-decoration: none; color: #333; font-weight: 500; transition: color 0.3s; }
    .navmenu ul li a:hover, .navmenu ul li a.active { color: #d32f2f; }
    .account-container { max-width: 500px; margin: 50px auto; background-color: #fff; padding: 30px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: right; }
    .account-container h2 { margin-bottom: 25px; color: #ce1212; text-align: center; }
    .profile-pic { text-align: center; margin-bottom: 25px; position: relative; }
    .profile-pic img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #ce1212; transition: all 0.3s ease; }
    .profile-pic:hover img { transform: scale(1.05); }
    .upload-btn { display: inline-block; margin-top: 10px; font-size: 14px; color: #ce1212; cursor: pointer; text-decoration: brown; transform: scale(1.05);}
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 6px; font-weight: 500; }
    .form-group input, .form-group select { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; transition: border 0.3s; }
    .form-group input:focus, .form-group select:focus { border-color: #ce1212; outline: none; }
    .form-actions { display: flex; justify-content: space-between; gap: 10px; margin-top: 25px; }
    .save-btn { background-color: #ce1212; color: #fff; border: none; padding: 12px 30px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: background 0.3s; transition: all 0.3s ease;}
    .save-btn:hover { background-color: #a01010; transform: scale(1.05); }
    .cancel-btn { background-color: #aaa; color: #fff; border: none; padding: 12px 30px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: background 0.3s; transition: all 0.3s ease;}
    .cancel-btn:hover { background-color: #888; transform: scale(1.05); }
    .footer { padding: 40px 20px 20px; box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05); margin-top: 50px; text-align: center; }
    .footer-content { display: flex; flex-wrap: wrap; justify-content: center; gap: 40px; max-width: 960px; margin: auto; padding-bottom: 20px; border-bottom: 1px solid #ffffff; }
    .footer-section h3 { color: #d32f2f; margin-bottom: 10px; font-size: 18px; }
    .footer-section p { margin: 5px 0; color: #484646; line-height: 1.6; font-size: 14px; }
    .footer .social-icons { margin-top: 10px; color: #ce1212; }
    .footer .social-icons a { margin: 0 5px; color: #d32f2f; font-size: 18px; transition: 0.3s ease; }
    .footer .social-icons a:hover { color: #010000; }
    .footer-bottom { margin-top: 20px; font-size: 13px; color: #484646; }
    .alert { padding: 12px; margin: 0 auto 20px; border-radius: 8px; text-align: center; max-width: 80%; }
    .alert-success { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
    .alert-error { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
    .password-container { position: relative; }
    .password-toggle { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #777; }
    @media (max-width: 768px) {
      
      .header { flex-direction: column; padding: 15px; }
      .navmenu ul { margin-top: 15px; }
    }
  </style>
</head>
<body>
<header class="header">
  <div class="logo">
    <h1>لقمة <span>هنية</span></h1>
  </div>
  <nav class="navmenu">
    <ul>
      <li><a href="/luqma/chefhome.php">صفحتي</a></li>
      <li><a href="/LUQMA/chefAndMeal/chefs.php">الطهاة</a></li>
      <li><a href="/luqma/login/chef.html" class="active"> تسجيل الدخول كطاه</a></li>
    </ul>
  </nav>
</header>
<main>
  <div class="account-container">
    <h2>إعدادات الحساب</h2>

    <?php if ($success): ?>
      <div class="alert alert-success">تم تحديث المعلومات بنجاح!</div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="update_chef_account.php" method="POST" enctype="multipart/form-data" id="chefForm">
      <div class="profile-pic">
        <img src="../uploads/<?= htmlspecialchars($profile_picture) ?>" 
             alt="صورة الحساب" 
             id="profileImage"
             onerror="this.src='../uploads/default-profile.png'"/>
        <input type="file" id="uploadImage" name="profile_picture" accept="image/*" hidden /><BR>
        <label for="uploadImage" class="upload-btn">تغيير الصورة</label>
      </div>

      <div class="form-group">
        <label for="name">الاسم الكامل</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($chef['name']) ?>" required/>
      </div>

        <div class="form-group">
        <label for="email">البريد الإلكتروني</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($chef['email']) ?>" required/>
      </div>

      <div class="form-group">
        <label for="phone">رقم الهاتف</label>
        <input type="text" id="phone" name="phone" placeholder="أدخل رقم الهاتف (10 أرقام)" pattern="\d{10}" maxlength="10" 
               value="<?= htmlspecialchars($chef['phone']) ?>" required/>
      </div>


      <div class="form-group">
        <label for="category_id">نوع الطباخ</label>
        <select id="category_id" name="category_id">
          <?php
            $categories = $pdo->query("SELECT id, name FROM categories")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($categories as $cat) {
                $selected = $chef['category_id'] == $cat['id'] ? 'selected' : '';
                echo "<option value='{$cat['id']}' $selected>" . htmlspecialchars($cat['name']) . "</option>";
            }
          ?>
        </select>
      </div>

      <div class="form-group">
  <label for="governorate_id">المحافظة</label>
  <select id="governorate_id" name="governorate_id">
    <?php
      $governorates = $pdo->query("SELECT id, name FROM governorates")->fetchAll(PDO::FETCH_ASSOC);
      foreach ($governorates as $gov) {
          $selected = $chef['governorate_id'] == $gov['id'] ? 'selected' : '';
          echo "<option value='{$gov['id']}' $selected>" . htmlspecialchars($gov['name']) . "</option>";
      }
    ?>
  </select>
</div>


      <div class="form-group password-container">
        <label for="password">كلمة المرور الجديدة</label>
        <input type="password" id="password" name="password" placeholder="اتركها فارغة إذا لم ترغب في التغيير"/>
      </div>

      <div class="form-actions">
        <button type="submit" class="save-btn">حفظ التغييرات</button>
        <button type="reset" class="cancel-btn">إلغاء</button>
      </div>
    </form>
  </div>
</main>
<footer class="footer">
  <div class="footer-content">
    <div class="footer-section contact-info">
      <h3>العنوان</h3>
      <p>الأردن</p>
      <p>عمان</p>
    </div>
    <div class="footer-section contact-methods">
      <h3>تواصل</h3>
      <p><strong>الهاتف:</strong> +962 55 5889 5589</p>
      <p><strong>البريد:</strong> info@example.com</p>
    </div>
    <div class="footer-section social">
      <h3>تابعنا</h3>
      <div class="social-icons">
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© 2025 جميع الحقوق محفوظة | <span>لقمة هنية</span></p>
    <p>Designed by <strong>Group 3</strong></p>
  </div>
</footer>

<script>
  // Profile picture preview
  document.getElementById('uploadImage').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        document.getElementById('profileImage').src = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  });

  

  // Auto-hide messages after 5 seconds
  setTimeout(function() {
    const messages = document.querySelectorAll('.alert');
    messages.forEach(msg => {
      msg.style.opacity = '0';
      setTimeout(() => msg.remove(), 500);
    });
  }, 5000);

  // Prevent form resubmission on refresh
  if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
  }
</script>

</body>
</html>