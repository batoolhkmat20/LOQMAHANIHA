<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login/user.html");
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=luqma;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$error = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $password = trim($_POST["password"]);
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "البريد الإلكتروني غير صحيح";
    } else {
        try {
            // Check if email already exists (excluding current user)
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION["user_id"]]);
            
            if ($stmt->rowCount() > 0) {
                $error = "البريد الإلكتروني مسجل بالفعل لحساب آخر";
            } else
             {

                if (empty($error)) {
                    // Prepare the update query
                    $sql = "UPDATE users SET name = ?, email = ?, phone = ?";
                    $params = [$name, $email, $phone];
                    
                    // Add password to update if provided
                    if (!empty($password)) {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $sql .= ", password = ?";
                        $params[] = $hashed_password;
                    }
                    
                    
                    
                    $sql .= " WHERE id = ?";
                    $params[] = $_SESSION["user_id"];
                    
                    // Execute the update
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    
                    $success = true;
                }
            }
        } catch (PDOException $e) {
            $error = "حدث خطأ أثناء تحديث البيانات: " . $e->getMessage();
        }
    }
    
    // Handle redirection with success/error messages
    $redirect_url = "/luqma/login/php/user_account/settings_account.php";
    if ($success) {
        $redirect_url .= "?success=1";
    } elseif ($error) {
        $redirect_url .= "?error=" . urlencode($error);
    }
    
    header("Location: " . $redirect_url);
    exit;
}

// If not a POST request, redirect back
header("Location: /luqma/login/php/user_account/settings_account.php");
exit;
?>