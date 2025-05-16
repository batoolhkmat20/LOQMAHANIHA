<?php
session_start();
require_once "db_connection.php";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "<script>localStorage.setItem('loginError', 'Database connection failed. Please try again later.'); window.location.href = '/luqma/login/chef.html';</script>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        echo "<script>localStorage.setItem('loginError', 'Please enter both email and password.'); window.location.href = '/luqma/login/chef.html';</script>";
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, name, password FROM chefs WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() === 1) {
        $chef = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($password, $chef["password"])) {
            $_SESSION["chef_id"] = $chef["id"];
            $_SESSION["chef_name"] = $chef["name"];
            header("Location: /luqma/chefHome.php");
            exit;
        } else {
            echo "<script>localStorage.setItem('loginError', 'كلمة المرور غير صحيحة.'); window.location.href = '/luqma/login/chef.html';</script>";
            exit;
        }
    } else {
        echo "<script>localStorage.setItem('loginError', 'لا يوجد حساب مرتبط بهذا البريد الإلكتروني.'); window.location.href = '/luqma/login/chef.html';</script>";
        exit;
    }
} else {
    echo "<script>localStorage.setItem('loginError', 'طريقة الطلب غير صالحة.'); window.location.href = '/luqma/login/chef.html';</script>";
    exit;
}