<?php
session_start();

if (!isset($_SESSION["chef_id"])) {
    header("Location: ../login/chef.html");
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=luqma;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $chef_id = $_SESSION["chef_id"];

    // جلب بيانات الشيف الحالية
    $stmt = $pdo->prepare("SELECT profile_picture FROM chefs WHERE id = ?");
    $stmt->execute([$chef_id]);
    $oldData = $stmt->fetch(PDO::FETCH_ASSOC);
    $oldPicture = $oldData['profile_picture'] ?? 'default-profile.png';

    // بيانات النموذج
    $name = $_POST["name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $category_id = $_POST["category_id"];
    $governorate_id = $_POST["governorate_id"];
    $password = $_POST["password"];

    // التعامل مع صورة الحساب
                // Handle profile picture upload
                $profile_picture = $oldPicture;

                if (isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] !== UPLOAD_ERR_NO_FILE) {
                    $file = $_FILES["profile_picture"];
                
                    if ($file["error"] === UPLOAD_ERR_OK) {
                        // Validate file type and size
                        $allowed_types = ["image/jpeg", "image/png", "image/gif", "image/webp"];
                        $max_size = 2 * 1024 * 1024; // 2MB
                
                        $file_info = finfo_open(FILEINFO_MIME_TYPE);
                        $mime_type = finfo_file($file_info, $file["tmp_name"]);
                        finfo_close($file_info);
                
                        if (in_array($mime_type, $allowed_types) && $file["size"] <= $max_size) {
                            $upload_dir = "../uploads/";
                            if (!is_dir($upload_dir)) {
                                mkdir($upload_dir, 0755, true);
                            }
                
                            $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
                            $filename = uniqid() . "." . $ext;
                            $destination = $upload_dir . $filename;
                
                            if (move_uploaded_file($file["tmp_name"], $destination)) {
                                $profile_picture = $filename;
                
                                // Delete old profile picture if it's not the default
                                if ($oldPicture && $oldPicture != "default-profile.png" && file_exists($upload_dir . $oldPicture)) {
                                    unlink($upload_dir . $oldPicture);
                                }
                            }
                        } else {
                            $error = "يجب أن تكون الصورة من نوع JPG أو PNG أو GIF ولا تزيد عن 2MB";
                        }
                    } else {
                        $error = "حدث خطأ أثناء رفع الصورة";
                    }
                }
                
    // تحديث البيانات
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE chefs SET name = ?, email = ?, phone = ?, category_id = ?, governorate_id = ?, profile_picture = ?, password = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $category_id, $governorate_id, $profile_picture, $hashedPassword, $chef_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE chefs SET name = ?, email = ?, phone = ?, category_id = ?, governorate_id = ?, profile_picture = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $category_id, $governorate_id, $profile_picture, $chef_id]);
    }        

    header("Location: chef_account.php?success=1");
    exit;

} catch (Exception $e) {
    header("Location: chef_account.php?error=" . urlencode("حدث خطأ في تحديث البيانات"));
    exit;
}
?>
