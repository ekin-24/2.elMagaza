<?php
session_start();
include 'baglan.php';

if (!isset($_SESSION['uyeId']) || !isset($_GET['id'])) {
    header("Location: index.php?hata=gecersizistek");
    exit();
}

$urunId = $_GET['id'];
$aliciId = $_SESSION['uyeId'];

try {
    $db->beginTransaction();


    $urunSorgu = $db->prepare("SELECT * FROM urunler WHERE urunId = ? FOR UPDATE");
    $urunSorgu->execute([$urunId]);
    $urun = $urunSorgu->fetch(PDO::FETCH_ASSOC);

    if (!$urun) {
        $db->rollBack();
        header("Location: index.php?hata=urunbulunamadi");
        exit();
    }


    if ($urun['satildi'] == 1) {
        $db->rollBack();
        header("Location: index.php?hata=urunzatensatildi");
        exit();
    }


    if ($urun['uyeId'] == $aliciId) {
        $db->rollBack();
        header("Location: index.php?hata=kendiurunu");
        exit();
    }

    $satisSorgu = $db->prepare("INSERT INTO satislar (urunId, saticiId, aliciId, satisTarihi) VALUES (?, ?, ?, NOW())");
    if (!$satisSorgu->execute([$urunId, $urun['uyeId'], $aliciId])) {
        $db->rollBack();
        header("Location: index.php?hata=satiskaydi");
        exit();
    }
    $satisId = $db->lastInsertId();


    $odemeSorgu = $db->prepare("INSERT INTO odemeler (satisId, odemeTutari, odemeTarihi) VALUES (?, ?, NOW())");
    if (!$odemeSorgu->execute([$satisId, $urun['urunFiyat']])) {
        $db->rollBack();
        header("Location: index.php?hata=odemekaydi");
        exit();
    }


    $urunGuncelle = $db->prepare("UPDATE urunler SET satildi = 1 WHERE urunId = ?");
    if (!$urunGuncelle->execute([$urunId])) {
        $db->rollBack();
        header("Location: index.php?hata=urunguncelleme");
        exit();
    }


    $db->commit();
    header("Location: index.php?durum=satinalindi");

} catch(PDOException $e) {
    $db->rollBack();
    error_log("Satın alma hatası: " . $e->getMessage());
    header("Location: index.php?hata=islembasarisiz");
}
exit(); 