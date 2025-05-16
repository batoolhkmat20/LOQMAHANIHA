<?php
session_start();
session_destroy();
header('Location: ../chef.html');
exit;
?>