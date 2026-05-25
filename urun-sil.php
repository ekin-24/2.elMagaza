<?php
session_start();
include 'baglan.php';

if (!isset($_SESSION['uyeId']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$urunId = $_GET['id'];
$uyeId = $_SESSION['uyeId'];

try {
    $kontrol = $db->prepare("SELECT uyeId FROM urunler WHERE urunId = ?");
    $kontrol->execute([$urunId]);
    $urun = $kontrol->fetch(PDO::FETCH_ASSOC);

    if ($urun && $urun['uyeId'] == $uyeId) {
        $sorgu = $db->prepare("CALL urunSil(?)");
        $sorgu->execute([$urunId]);
        header("Location: urunlerim.php?durum=silindi");
    } else {
        header("Location: urunlerim.php?hata=yetki");
    }
} catch(PDOException $e) {
    header("Location: urunlerim.php?hata=silinemedi");
}
exit(); 