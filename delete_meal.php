<?php
require_once 'db.php';

$meal_id = $_GET['id'] ?? null;

if ($meal_id) {
    // حذف التعليقات المرتبطة أولاً
    $pdo->prepare("DELETE FROM reviews WHERE meal_id = ?")->execute([$meal_id]);

    // بعدها حذف الطبق
    $stmt = $pdo->prepare("DELETE FROM meals WHERE id = ?");
    $stmt->execute([$meal_id]);
}

header("Location: chefHome.php");
exit;

?>
