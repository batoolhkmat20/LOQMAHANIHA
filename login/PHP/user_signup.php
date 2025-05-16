<?php
session_start();

// Database configuration
require_once "db_connection.php";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "<script>localStorage.setItem('signupError', 'فشل الاتصال بقاعدة البيانات. يرجى المحاولة لاحقًا.'); window.location.href = '/luqma/login/user.html';</script>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize and validate inputs
    $name = trim($_POST["name"]);
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Validate required fields
    if (empty($name) || empty($phone) || empty($email) || empty($password)) {
        echo "<script>localStorage.setItem('signupError', 'جميع الحقول المطلوبة يجب تعبئتها.'); window.location.href = '/luqma/login/user.html';</script>";
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>localStorage.setItem('signupError', 'صيغة البريد الإلكتروني غير صالحة.'); window.location.href = '/luqma/login/user.html';</script>";
        exit;
    }

    // Check for duplicate email
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            echo "<script>localStorage.setItem('signupError', 'البريد الإلكتروني مسجل مسبقًا.'); window.location.href = '/luqma/login/user.html';</script>";
            exit;
        }
    } catch (PDOException $e) {
        echo "<script>localStorage.setItem('signupError', 'حدث خطأ أثناء التحقق من البريد الإلكتروني.'); window.location.href = '/luqma/login/user.html';</script>";
        exit;
    }

    // Hash password and insert user
    try {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
        $stmt = $pdo->prepare("INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $phone, $email, $hashedPassword]);
        
        // Successful registration
        header("Location: /luqma/index.php");
        exit;
    } catch (PDOException $e) {
        echo "<script>localStorage.setItem('signupError', 'حدث خطأ أثناء التسجيل. يرجى المحاولة لاحقًا.'); window.location.href = '/luqma/login/user.html';</script>";
        exit;
    }
} else {
    echo "<script>localStorage.setItem('signupError', 'طلب غير صالح.'); window.location.href = '/luqma/login/user.html';</script>";
    exit;
}
?>