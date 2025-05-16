<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "لم يتم تسجيل الدخول."]);
    exit();
}

$userId = $_SESSION['user_id'];

$host = 'localhost';
$db   = 'luqma';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents("php://input"), true);

    if (
        !isset($data['order_id'], $data['meal_id'], $data['chef_id'], $data['meal_rating'], $data['chef_rating'])
        || !is_numeric($data['meal_rating']) || !is_numeric($data['chef_rating'])
    ) {
        echo json_encode(["status" => "error", "message" => "بيانات التقييم غير صالحة."]);
        exit();
    }

    // منع التقييم المكرر لنفس الطلب والوجبة والشيف
    $check = $conn->prepare("SELECT COUNT(*) FROM order_ratings WHERE order_id = ? AND meal_id = ? AND chef_id = ?");
    $check->execute([$data['order_id'], $data['meal_id'], $data['chef_id']]);
    if ($check->fetchColumn() > 0) {
        echo json_encode(["status" => "error", "message" => "تم تقييم هذا الطلب مسبقًا."]);
        exit();
    }

    $stmt = $conn->prepare("
        INSERT INTO order_ratings (order_id, meal_id, chef_id, user_id, meal_rating, chef_rating)
        VALUES (:order_id, :meal_id, :chef_id, :user_id, :meal_rating, :chef_rating)
    ");
    $stmt->execute([
        'order_id' => $data['order_id'],
        'meal_id' => $data['meal_id'],
        'chef_id' => $data['chef_id'],
        'user_id' => $userId,
        'meal_rating' => $data['meal_rating'],
        'chef_rating' => $data['chef_rating']
    ]);

    echo json_encode(["status" => "success", "message" => "تم حفظ التقييم بنجاح."]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "فشل في الحفظ: " . $e->getMessage()]);
}
?>
