<?php
session_start();
include 'baglan.php';

if (!isset($_SESSION['uyeId'])) {
    header("Location: index.php");
    exit();
}

$uyeId = $_SESSION['uyeId'];
$urunler = $db->prepare("SELECT * FROM urunler WHERE uyeId = ? AND satildi = 0");
$urunler->execute([$uyeId]);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürünlerim - 2.El Alım Satım</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Ürünlerim</h1>
            <nav>
                <a href="index.php">Ana Sayfa</a>
                <a href="urunlerim.php">Ürünlerim</a>
                <a href="urun-ekle.php">Ürün Ekle</a>
                <a href="profilim.php">Profilim</a>
                <a href="cikis.php">Çıkış Yap</a>
            </nav>
        </header>

        <main>
            <?php if(isset($_GET['durum']) && $_GET['durum'] == 'eklendi'): ?>
            <div class="success">Ürün başarıyla eklendi!</div>
            <?php endif; ?>

            <section class="urunler">
                <?php if($urunler->rowCount() > 0): ?>
                    <?php while($urun = $urunler->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="urun-card">
                            <h3><?php echo htmlspecialchars($urun['urunAdi']); ?></h3>
                            <p class="fiyat"><?php echo number_format($urun['urunFiyat'], 2, ',', '.'); ?> TL</p>
                            <p class="kdv">KDV'li Fiyat: <?php 
                                $kdvliFiyat = $db->query("SELECT kdvliFiyat({$urun['urunFiyat']}) as kdvli")->fetch(PDO::FETCH_ASSOC);
                                echo number_format($kdvliFiyat['kdvli'], 2, ',', '.'); 
                            ?> TL</p>
                            <div class="urun-islemler">
                                <a href="urun-duzenle.php?id=<?php echo $urun['urunId']; ?>" class="duzenle">Düzenle</a>
                                <a href="urun-sil.php?id=<?php echo $urun['urunId']; ?>" class="sil" onclick="return confirm('Bu ürünü silmek istediğinizden emin misiniz?')">Sil</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-products">Henüz ürün eklememişsiniz. <a href="urun-ekle.php">Hemen ürün ekleyin!</a></p>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html> 