<?php
session_start();
include 'baglan.php';

if (!isset($_SESSION['uyeId'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $urunAdi = $_POST['urunAdi'];
    $urunFiyati = $_POST['urunFiyati'];
    $uyeId = $_SESSION['uyeId'];

    try {
        $sorgu = $db->prepare("INSERT INTO urunler (urunAdi, urunFiyat, uyeId) VALUES (?, ?, ?)");
        $sorgu->execute([$urunAdi, $urunFiyati, $uyeId]);
        header("Location: urunlerim.php?durum=eklendi");
        exit();
    } catch(PDOException $e) {
        header("Location: urun-ekle.php?hata=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Ekle - 2.El Alım Satım</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Ürün Ekle</h1>
            <nav>
                <a href="index.php">Ana Sayfa</a>
                <a href="urunlerim.php">Ürünlerim</a>
                <a href="urun-ekle.php">Ürün Ekle</a>
                <a href="profilim.php">Profilim</a>
                <a href="cikis.php">Çıkış Yap</a>
            </nav>
        </header>

        <main>
            <div class="form-container">
                <?php if(isset($_GET['hata'])): ?>
                <div class="error">Ürün eklenirken bir hata oluştu. Lütfen tekrar deneyin.</div>
                <?php endif; ?>

                <form action="urun-ekle.php" method="POST">
                    <div class="form-group">
                        <label for="urunAdi">Ürün Adı:</label>
                        <input type="text" id="urunAdi" name="urunAdi" required>
                    </div>

                    <div class="form-group">
                        <label for="urunFiyati">Ürün Fiyatı (TL):</label>
                        <input type="number" id="urunFiyati" name="urunFiyati" min="1" required>
                    </div>

                    <button type="submit">Ürün Ekle</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html> 