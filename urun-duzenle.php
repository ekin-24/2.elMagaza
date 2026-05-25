<?php
session_start();
include 'baglan.php';

if (!isset($_SESSION['uyeId']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$urunId = $_GET['id'];
$uyeId = $_SESSION['uyeId'];

$urunSorgu = $db->prepare("SELECT * FROM urunler WHERE urunId = ? AND uyeId = ?");
$urunSorgu->execute([$urunId, $uyeId]);
$urun = $urunSorgu->fetch(PDO::FETCH_ASSOC);

if (!$urun) {
    header("Location: urunlerim.php?hata=yetki");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $yeniAd = $_POST['urunAdi'];
    $yeniFiyat = $_POST['urunFiyati'];

    try {
        $sorgu = $db->prepare("CALL urunFiyatGuncelle(?, ?)");
        $sorgu->execute([$urunId, $yeniFiyat]);
        
        $sorgu = $db->prepare("UPDATE urunler SET urunAdi = ? WHERE urunId = ?");
        $sorgu->execute([$yeniAd, $urunId]);

        header("Location: urunlerim.php?durum=guncellendi");
        exit();
    } catch(PDOException $e) {
        header("Location: urun-duzenle.php?id=" . $urunId . "&hata=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Düzenle - 2.El Alım Satım</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Ürün Düzenle</h1>
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
                <div class="error">Ürün güncellenirken bir hata oluştu. Lütfen tekrar deneyin.</div>
                <?php endif; ?>

                <form action="urun-duzenle.php?id=<?php echo $urunId; ?>" method="POST">
                    <div class="form-group">
                        <label for="urunAdi">Ürün Adı:</label>
                        <input type="text" id="urunAdi" name="urunAdi" value="<?php echo htmlspecialchars($urun['urunAdi']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="urunFiyati">Ürün Fiyatı (TL):</label>
                        <input type="number" id="urunFiyati" name="urunFiyati" value="<?php echo $urun['urunFiyat']; ?>" min="1" required>
                    </div>

                    <button type="submit">Değişiklikleri Kaydet</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html> 