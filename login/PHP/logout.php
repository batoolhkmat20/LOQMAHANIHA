<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الخروج</title>
</head>
<body>
    <script>
        // مسح جميع البيانات المخزنة في localStorage
        localStorage.clear();

        // إعادة التوجيه إلى الصفحة الرئيسية أو أي صفحة أخرى بعد مسح البيانات
        window.location.href = "/luqma/index.php";
    </script>
</body>
</html>
