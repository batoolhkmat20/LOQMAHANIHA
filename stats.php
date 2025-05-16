<?php
include 'db.php';

// عدد الطهاة
$chefCount = $pdo->query("SELECT COUNT(*) AS total FROM chefs")->fetch(PDO::FETCH_ASSOC)['total'];

// عدد الأطباق
$mealCount = $pdo->query("SELECT COUNT(*) AS total FROM meals")->fetch(PDO::FETCH_ASSOC)['total'];

// عدد الطلبات
$orderCount = $pdo->query("SELECT COUNT(*) AS total FROM order_ratings")->fetch(PDO::FETCH_ASSOC)['total'];
?>
