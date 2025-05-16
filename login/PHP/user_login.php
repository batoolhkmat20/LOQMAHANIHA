<?php
session_start();
require_once "db_connection.php";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "<script>localStorage.setItem('loginError', 'Database connection failed. Please try again later.'); window.location.href = '/luqma/login/user.html';</script>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        echo "<script>localStorage.setItem('loginError', 'Please enter both email and password.'); window.location.href = '/luqma/login/user.html';</script>";
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($password, $user["password"])) {
            // حفظ البيانات في الجلسة
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["role"] = $user["role"];

            // حفظ بيانات المستخدم في localStorage
            $userDataJS = json_encode([
                'id' => $user['id'],
                'name' => $user['name'],
                'role' => $user['role']
            ]);

            if ($user["role"] == 1) {
                echo "<script>
                    localStorage.setItem('user', '$userDataJS');
                    window.location.href = '/luqma/index.php';
                </script>";
            } elseif ($user["role"] == 2) {
                echo "<script>
                    localStorage.setItem('user', '$userDataJS');
                    window.location.href = '/luqma/admin-dashboard/dashboard.html';
                </script>";
            } else {
                echo "<script>
                    localStorage.setItem('loginError', 'دور المستخدم غير صالح.');
                    window.location.href = '/luqma/login/user.html';
                </script>";
            }
            exit;
        } else {
            echo "<script>
                localStorage.setItem('loginError', 'كلمة المرور غير صحيحة.');
                window.location.href = '/luqma/login/user.html';
            </script>";
            exit;
        }
    } else {
        echo "<script>
            localStorage.setItem('loginError', 'لا يوجد حساب بهذا البريد الإلكتروني.');
            window.location.href = '/luqma/login/user.html';
        </script>";
        exit;
    }
} else {
    echo "<script>
        localStorage.setItem('loginError', 'طلب غير صالح.');
        window.location.href = '/luqma/login/user.html';
    </script>";
    exit;
}
?>