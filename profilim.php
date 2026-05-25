<?php
session_start();
include 'baglan.php';

if (!isset($_SESSION['uyeId'])) {
    header("Location: index.php");
    exit();
}

$uyeId = $_SESSION['uyeId'];
$mesaj = '';
$mesajTur = '';

try {
    $uyeSorgu = $db->prepare("SELECT * FROM uyeler WHERE uyeId = ? LIMIT 1");
    $uyeSorgu->execute([$uyeId]);
    $uye = $uyeSorgu->fetch(PDO::FETCH_ASSOC);
    
    if (!$uye) {
        session_destroy();
        header("Location: index.php?hata=kullanici_bulunamadi");
        exit();
    }
} catch(PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $yeniAd = trim(filter_input(INPUT_POST, 'ad', FILTER_SANITIZE_STRING));
    $yeniSoyad = trim(filter_input(INPUT_POST, 'soyad', FILTER_SANITIZE_STRING));
    $yeniTel = trim(filter_input(INPUT_POST, 'telefon', FILTER_SANITIZE_STRING));
    $yeniAdres = trim(filter_input(INPUT_POST, 'adres', FILTER_SANITIZE_STRING));
    $yeniSifre = trim(filter_input(INPUT_POST, 'sifre', FILTER_SANITIZE_STRING));
    $mevcutSifre = trim(filter_input(INPUT_POST, 'mevcut_sifre', FILTER_SANITIZE_STRING));

    if ($mevcutSifre === $uye['uyeSifre']) {
        $telKontrol = $db->prepare("SELECT uyeId FROM uyeler WHERE uyeTel = ? AND uyeId != ?");
        $telKontrol->execute([$yeniTel, $uyeId]);
        
        if ($telKontrol->rowCount() > 0) {
            $mesaj = "Bu telefon numarası başka bir kullanıcı tarafından kullanılıyor!";
            $mesajTur = "error";
        } else {
            try {
                $db->beginTransaction();

                $guncelle = $db->prepare("UPDATE uyeler SET uyeAd = ?, uyeSoyad = ?, uyeTel = ?, uyeAdres = ?, uyeSifre = ? WHERE uyeId = ?");
                if ($guncelle->execute([$yeniAd, $yeniSoyad, $yeniTel, $yeniAdres, $yeniSifre, $uyeId])) {
                    $db->commit();
                    $mesaj = "Profil bilgileriniz başarıyla güncellendi!";
                    $mesajTur = "success";
                    
                    $_SESSION['uyeAd'] = $yeniAd;
                    $_SESSION['uyeSoyad'] = $yeniSoyad;
                    
                    $uyeSorgu->execute([$uyeId]);
                    $uye = $uyeSorgu->fetch(PDO::FETCH_ASSOC);
                } else {
                    $db->rollBack();
                    $mesaj = "Güncelleme sırasında bir hata oluştu!";
                    $mesajTur = "error";
                }
            } catch(PDOException $e) {
                $db->rollBack();
                $mesaj = "Bir hata oluştu: " . $e->getMessage();
                $mesajTur = "error";
            }
        }
    } else {
        $mesaj = "Mevcut şifreniz hatalı!";
        $mesajTur = "error";
    }
}

try {
    $satinAlinanlar = $db->prepare("
        SELECT u.*, uy.uyeAd as saticiAd, uy.uyeSoyad as saticiSoyad, o.odemeTutari, o.satildi, s.satisId
        FROM satislar s
        JOIN urunler u ON s.urunId = u.urunId
        JOIN uyeler uy ON s.saticiId = uy.uyeId
        JOIN odemeler o ON s.satisId = o.satisId
        WHERE s.aliciId = ?
        ORDER BY s.satisId DESC
    ");
    $satinAlinanlar->execute([$uyeId]);
} catch(PDOException $e) {
    $satinAlinanlar = null;
    $mesaj = "Satın alınan ürünler yüklenirken hata oluştu.";
    $mesajTur = "error";
}


    $satilanlar = $db->prepare("
        SELECT u.*, uy.uyeAd as aliciAd, uy.uyeSoyad as aliciSoyad, o.odemeTutari, o.satildi, s.satisId
        FROM satislar s
        JOIN urunler u ON s.urunId = u.urunId
        JOIN uyeler uy ON s.aliciId = uy.uyeId
        JOIN odemeler o ON s.satisId = o.satisId
        WHERE s.saticiId = ?
        ORDER BY s.satisId DESC
    ");
    $satilanlar->execute([$uyeId]);
} catch(PDOException $e) {
    $satilanlar = null;
    $mesaj = "Satılan ürünler yüklenirken hata oluştu.";
    $mesajTur = "error";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilim - 2.El Alım Satım</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Profilim</h1>
            <nav>
                <a href="index.php">Ana Sayfa</a>
                <a href="urunlerim.php">Ürünlerim</a>
                <a href="urun-ekle.php">Ürün Ekle</a>
                <a href="profilim.php">Profilim</a>
                <a href="cikis.php">Çıkış Yap</a>
            </nav>
        </header>

        <main>
            <?php if($mesaj): ?>
                <div class="<?php echo $mesajTur; ?>"><?php echo $mesaj; ?></div>
            <?php endif; ?>

            <div class="profil-bilgileri">
                <h2>Profil Bilgilerim</h2>
                <?php if($uye): ?>
                <form method="POST" class="profil-form" onsubmit="return validateForm()">
                    <div class="form-group">
                        <label for="ad">Adınız:</label>
                        <input type="text" id="ad" name="ad" value="<?php echo htmlspecialchars($uye['uyeAd']); ?>" required minlength="2" maxlength="50">
                    </div>

                    <div class="form-group">
                        <label for="soyad">Soyadınız:</label>
                        <input type="text" id="soyad" name="soyad" value="<?php echo htmlspecialchars($uye['uyeSoyad']); ?>" required minlength="2" maxlength="50">
                    </div>

                    <div class="form-group">
                        <label for="telefon">Telefon:</label>
                        <input type="tel" id="telefon" name="telefon" value="<?php echo htmlspecialchars($uye['uyeTel']); ?>" required pattern="[0-9]{10,11}" title="Lütfen geçerli bir telefon numarası giriniz">
                    </div>

                    <div class="form-group">
                        <label for="email">E-posta:</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($uye['uyeMail']); ?>" disabled>
                        <small>E-posta adresi değiştirilemez.</small>
                    </div>

                    <div class="form-group">
                        <label for="adres">Adres:</label>
                        <textarea id="adres" name="adres" required minlength="10" maxlength="100"><?php echo htmlspecialchars($uye['uyeAdres']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="mevcut_sifre">Mevcut Şifre:</label>
                        <input type="password" id="mevcut_sifre" name="mevcut_sifre" required minlength="6" maxlength="50">
                        <small>Değişiklikleri onaylamak için mevcut şifrenizi giriniz.</small>
                    </div>

                    <div class="form-group">
                        <label for="sifre">Yeni Şifre:</label>
                        <input type="password" id="sifre" name="sifre" required minlength="6" maxlength="50">
                        <small>En az 6 karakter uzunluğunda olmalıdır.</small>
                    </div>

                    <button type="submit" name="profil_guncelle">Bilgilerimi Güncelle</button>
                </form>
                <?php else: ?>
                <div class="error">Kullanıcı bilgileri yüklenemedi. Lütfen tekrar giriş yapın.</div>
                <?php endif; ?>
            </div>

            <div class="islem-gecmisi">
                <h2>Satın Aldığım Ürünler</h2>
                <div class="islemler">
                    <?php if($satinAlinanlar && $satinAlinanlar->rowCount() > 0): ?>
                        <?php while($urun = $satinAlinanlar->fetch(PDO::FETCH_ASSOC)): ?>
                            <div class="islem-card">
                                <h3><?php echo htmlspecialchars($urun['urunAdi']); ?></h3>
                                <p><strong>Satıcı:</strong> <?php echo htmlspecialchars($urun['saticiAd'] . ' ' . $urun['saticiSoyad']); ?></p>
                                <p><strong>Fiyat:</strong> <?php echo number_format($urun['odemeTutari'], 2, ',', '.'); ?> TL</p>
                                <p class="durum <?php echo $urun['satildi'] ? 'tamamlandi' : 'beklemede'; ?>">
                                    <?php echo $urun['satildi'] ? 'Tamamlandı' : 'Beklemede'; ?>
                                </p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="no-items">Henüz ürün satın almadınız.</p>
                    <?php endif; ?>
                </div>

                <h2>Sattığım Ürünler</h2>
                <div class="islemler">
                    <?php if($satilanlar && $satilanlar->rowCount() > 0): ?>
                        <?php while($urun = $satilanlar->fetch(PDO::FETCH_ASSOC)): ?>
                            <div class="islem-card">
                                <h3><?php echo htmlspecialchars($urun['urunAdi']); ?></h3>
                                <p><strong>Alıcı:</strong> <?php echo htmlspecialchars($urun['aliciAd'] . ' ' . $urun['aliciSoyad']); ?></p>
                                <p><strong>Fiyat:</strong> <?php echo number_format($urun['odemeTutari'], 2, ',', '.'); ?> TL</p>
                                <p class="durum <?php echo $urun['satildi'] ? 'tamamlandi' : 'beklemede'; ?>">
                                    <?php echo $urun['satildi'] ? 'Tamamlandı' : 'Beklemede'; ?>
                                </p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="no-items">Henüz ürün satmadınız.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
    function validateForm() {
        var telefon = document.getElementById('telefon').value;
        var sifre = document.getElementById('sifre').value;
        var mevcutSifre = document.getElementById('mevcut_sifre').value;


        if (!/^[0-9]{10,11}$/.test(telefon)) {
            alert('Lütfen geçerli bir telefon numarası giriniz (10-11 rakam)');
            return false;
        }


        if (sifre.length < 6) {
            alert('Şifre en az 6 karakter uzunluğunda olmalıdır');
            return false;
        }


        if (mevcutSifre.length < 6) {
            alert('Lütfen mevcut şifrenizi giriniz');
            return false;
        }

        return true;
    }
    </script>
</body>
</html> 