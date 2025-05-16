
<?php
// db_connection.php
$host = 'localhost';
$db   = 'luqma';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=luqma;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
