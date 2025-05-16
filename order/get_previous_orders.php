<?php
session_start();

// تأكد من أن المستخدم قد قام بتسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "لم تقم بتسجيل الدخول."]);
    exit();
}

$user_id = $_SESSION['user_id']; // معرف المستخدم من الجلسة

$host = 'localhost';
$db   = 'luqma';
$user = 'root';
$pass = '';

header('Content-Type: application/json');

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // تعديل الاستعلام ليشمل فقط طلبات المستخدم الحالي
    $stmt = $conn->prepare("
    SELECT 
        o.id as order_id, 
        DATE_FORMAT(o.order_date, '%Y-%m-%d %H:%i:%s') as order_date,
        o.status, 
        o.total_price, 
        m.name as meal_name, 
        m.image as meal_image, 
        om.quantity, 
        om.price_per_unit as original_price,  
        om.price_after_discount, 
        c.name as chef_name 
    FROM orders o
    JOIN order_meals om ON o.id = om.order_id
    JOIN meals m ON om.meal_id = m.id
    JOIN chefs c ON m.chef_id = c.id
    WHERE o.user_id = :user_id  -- إضافة شرط للمستخدم
    ORDER BY o.order_date DESC
");

    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);  // ربط قيمة المستخدم بالاستعلام
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($orders)) {
        echo json_encode(["status" => "error", "message" => "لا توجد بيانات"]);
        exit();
    }

    $result = [];
    foreach ($orders as $order) {
        if (!isset($result[$order['order_id']])) {
            $result[$order['order_id']] = [
                'order_id' => $order['order_id'],
                'order_date' => $order['order_date'],
                'status' => $order['status'],
                'total_price' => $order['total_price'],
                'meals' => []
            ];
        }

        $result[$order['order_id']]['meals'][] = [
            'meal_name' => $order['meal_name'],
            'meal_image' => $order['meal_image'],
            'quantity' => $order['quantity'],
            'price' => $order['original_price'],  
            'price_after_discount' => $order['price_after_discount'],
            'chef_name' => $order['chef_name']
        ];
    }

    echo json_encode(["status" => "success", "orders" => array_values($result)]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "خطأ في قاعدة البيانات: " . $e->getMessage()]);
}

?>