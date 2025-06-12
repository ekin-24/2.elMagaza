<?php
session_start();
require_once 'config.php';

if(!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$urunId = $_GET['id'];

$stmt = $conn->prepare("SELECT urunler.*, uyeler.uyeAd, uyeler.uyeSoyad, uyeler.uyeTel, uyeler.uyeMail 
                       FROM urunler 
                       INNER JOIN uyeler ON urunler.uyeId = uyeler.uyeId 
                       WHERE urunId = ?");
$stmt->execute([$urunId]);
$urun = $stmt->fetch();

if(!$urun) {
    header("Location: index.php");
    exit();
}

if(isset($_POST['satinAl']) && isset($_SESSION['uyeId'])) {
    $aliciId = $_SESSION['uyeId'];
    $saticiId = $urun['uyeId'];
    
    try {
        $conn->beginTransaction();
        
        $stmt = $conn->prepare("INSERT INTO satislar (urunId, saticiId, aliciId) VALUES (?, ?, ?)");
        $stmt->execute([$urunId, $saticiId, $aliciId]);
        
        $satisId = $conn->lastInsertId();
        
        $stmt = $conn->prepare("INSERT INTO odemeler (satisId, odemeTutari) VALUES (?, ?)");
        $stmt->execute([$satisId, $urun['urunFiyatı']]);
        
        $stmt = $conn->prepare("UPDATE urunler SET satildi = 1 WHERE urunId = ?");
        $stmt->execute([$urunId]);
        
        $conn->commit();
        $basari = "Satın alma işlemi başarıyla tamamlandı!";
        
        header("refresh:2;url=index.php");
        
    } catch(PDOException $e) {
        $conn->rollBack();
        $hata = "İşlem sırasında bir hata oluştu: " . $e->getMessage();
    }
}

$stmt = $conn->prepare("SELECT satildi FROM urunler WHERE urunId = ?");
$stmt->execute([$urunId]);
$urunDurum = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($urun['urunAdi']); ?> - 2.El Mağaza</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">2.El Mağaza</div>
            <ul>
                <li><a href="index.php">Ana Sayfa</a></li>
                <?php if(isset($_SESSION['uyeId'])): ?>
                    <li><a href="urun-ekle.php">Ürün Ekle</a></li>
                    <li><a href="profilim.php">Profilim</a></li>
                    <li><a href="cikis.php">Çıkış Yap</a></li>
                <?php else: ?>
                    <li><a href="giris.php">Giriş Yap</a></li>
                    <li><a href="kayit.php">Kayıt Ol</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <div class="form-container" style="max-width: 800px;">
            <h2><?php echo htmlspecialchars($urun['urunAdi']); ?></h2>
            <?php if(isset($hata)): ?>
                <div class="error"><?php echo $hata; ?></div>
            <?php endif; ?>
            <?php if(isset($basari)): ?>
                <div class="success"><?php echo $basari; ?></div>
            <?php endif; ?>
            
            <div class="urun-detay">
                <h3>Ürün Bilgileri</h3>
                <p class="fiyat"><?php echo number_format($urun['urunFiyatı'], 2, ',', '.'); ?> TL</p>
                <?php if($urunDurum['satildi']): ?>
                    <p class="error" style="margin-top: 1rem;">Bu ürün satılmıştır.</p>
                <?php endif; ?>
                
                <div class="satici-bilgileri">
                    <h3>Satıcı Bilgileri</h3>
                    <p><strong>Ad Soyad:</strong> <?php echo htmlspecialchars($urun['uyeAd'] . ' ' . $urun['uyeSoyad']); ?></p>
                    <p><strong>Telefon:</strong> <?php echo htmlspecialchars($urun['uyeTel']); ?></p>
                    <p><strong>E-posta:</strong> <?php echo htmlspecialchars($urun['uyeMail']); ?></p>
                </div>

                <?php if(!$urunDurum['satildi']): ?>
                    <?php if(isset($_SESSION['uyeId']) && $_SESSION['uyeId'] != $urun['uyeId']): ?>
                        <form method="POST">
                            <button type="submit" name="satinAl" class="satin-al-btn">Satın Al</button>
                        </form>
                    <?php elseif(!isset($_SESSION['uyeId'])): ?>
                        <p style="text-align: center; margin-top: 1rem;">
                            Satın almak için <a href="giris.php">giriş yapın</a> veya <a href="kayit.php">kayıt olun</a>.
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 2.El Mağaza. Tüm hakları saklıdır.</p>
    </footer>
</body>
</html> 