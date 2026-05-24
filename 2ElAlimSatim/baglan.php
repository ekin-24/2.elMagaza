<?php
try {
    $db = new PDO("mysql:host=mysql-service;dbname=2elMagaza;charset=utf8", "root", "root");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Bağlantı hatası: " . $e->getMessage();
    die();
}
?>