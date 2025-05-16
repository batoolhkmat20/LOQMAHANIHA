<?php
// إعدادات الاتصال بقاعدة البيانات
$host = 'localhost';
$db   = 'luqma';
$user = 'root';
$pass = '';

header('Content-Type: application/json');

try {
    // الاتصال بقاعدة البيانات باستخدام PDO
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // قراءة البيانات بصيغة JSON من الطلب
    $data = json_decode(file_get_contents("php://input"), true);

    // التحقق من صحة البيانات الأساسية
    if (!$data || !isset($data['order']) || !isset($data['order_items'])) {
        echo json_encode(["status" => "error", "message" => "بيانات الطلب غير صالحة"]);
        exit;
    }

    $userId = $data['order']['user_id'];
    $totalPrice = $data['order']['total_price'];
    $orderItems = $data['order_items'];

    // التحقق من صحة معرف المستخدم
    if (!isset($userId) || !is_numeric($userId)) {
        echo json_encode(["status" => "error", "message" => "معرف المستخدم غير صالح"]);
        exit;
    }

    // جلب بيانات المستخدم (الاسم ورقم الهاتف)
    $stmt = $conn->prepare("SELECT name, phone FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        echo json_encode(["status" => "error", "message" => "المستخدم غير موجود"]);
        exit;
    }

    $userName = $userData['name'];
    $userPhone = $userData['phone'];

    // إنشاء سجل الطلب في جدول orders
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, order_date) VALUES (:user_id, :total_price, NOW())");
    $stmt->execute([
        'user_id' => $userId,
        'total_price' => $totalPrice
    ]);
    $orderId = $conn->lastInsertId();

    // مصفوفة لتجميع الطهاة المرتبطين بالوجبات مع تفاصيل الوجبات لكل شيف
    $chefsToNotify = [];

    // إدخال كل وجبة ضمن order_meals وجلب اسم الوجبة والسعر بعد الخصم من جدول meals
    foreach ($orderItems as $item) {
        $mealId = $item['meal_id'];
        $quantity = $item['quantity'];

        // جلب بيانات الوجبة من جدول meals (الاسم، السعر، الخصم)
        $stmt = $conn->prepare("SELECT chef_id, name, price, discount FROM meals WHERE id = :meal_id");
        $stmt->execute(['meal_id' => $mealId]);
        $mealData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$mealData) continue;

        $chefId = $mealData['chef_id'];
        $mealName = $mealData['name'];
        $price = $mealData['price'];
        $discount = $mealData['discount'];
        $priceAfterDiscount = $price - $discount;

        // إدخال بيانات الوجبة في order_meals
        $stmt = $conn->prepare("
            INSERT INTO order_meals (order_id, meal_id, quantity, price_per_unit, discount, price_after_discount)
            VALUES (:order_id, :meal_id, :quantity, :price_per_unit, :discount, :price_after_discount)
        ");
        $stmt->execute([
            'order_id' => $orderId,
            'meal_id' => $mealId,
            'quantity' => $quantity,
            'price_per_unit' => $price,
            'discount' => $discount,
            'price_after_discount' => $priceAfterDiscount * $quantity
        ]);

        // تخزين بيانات الوجبة لكل شيف (اسم الوجبة والسعر الكلي للكمية)
        if (!isset($chefsToNotify[$chefId])) {
            $chefsToNotify[$chefId] = [];
        }
        $chefsToNotify[$chefId][] = [
            'meal_name' => $mealName,
            'total_price' => $priceAfterDiscount * $quantity
        ];
    }

    // إرسال إشعارات للطهاة مع تفاصيل الوجبات وبيانات المستخدم
    foreach ($chefsToNotify as $chefId => $meals) {
        $mealDetails = [];
        foreach ($meals as $m) {
            $mealDetails[] = "{$m['meal_name']} (السعر: {$m['total_price']} د.أ)";
        }
        $mealList = implode(", ", $mealDetails);

        $notificationMessage = "لديك طلب جديد يرجى مراجعته:  " ;
                               
                              

        $stmt = $conn->prepare("
            INSERT INTO notifications (chef_id, message, is_read, created_at, order_id)
            VALUES (:chef_id, :message, 0, NOW(), :order_id)
        ");
        $stmt->execute([
            'chef_id' => $chefId,
            'message' => $notificationMessage,
            'order_id' => $orderId
        ]);
    }

    echo json_encode(["status" => "success", "message" => "تم حفظ الطلب بنجاح."]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "خطأ في قاعدة البيانات: " . $e->getMessage()]);
}
?>