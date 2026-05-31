<?php
session_start();
include 'baglan.php';
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.El Alım Satım</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>2.El Alım Satım Sitesi</h1>
            <?php if(isset($_SESSION['uyeId'])): ?>
                <nav>
                    <a href="index.php">Ana Sayfa</a>
                    <a href="urunlerim.php">Ürünlerim</a>
                    <a href="urun-ekle.php">Ürün Ekle</a>
                    <a href="profilim.php">Profilim</a>
                    <a href="cikis.php">Çıkış Yap</a>
                </nav>
            <?php else: ?>
            <div class="login-form">
                <form action="giris.php" method="POST">
                    <input type="email" name="email" placeholder="E-posta" required>
                    <input type="password" name="sifre" placeholder="Şifre" required>
                    <button type="submit">Giriş Yap</button>
                </form>
                <a href="#" onclick="toggleForms()">Hesabınız yok mu? Kayıt olun</a>
            </div>

            <div class="register-form" style="display: none;">
                <form action="kayit.php" method="POST">
                    <input type="text" name="ad" placeholder="Adınız" required>
                    <input type="text" name="soyad" placeholder="Soyadınız" required>
                    <input type="tel" name="telefon" placeholder="Telefon" required>
                    <input type="email" name="email" placeholder="E-posta" required>
                    <textarea name="adres" placeholder="Adres" required></textarea>
                    <input type="password" name="sifre" placeholder="Şifre" required>
                    <button type="submit">Kayıt Ol</button>
                </form>
                <a href="#" onclick="toggleForms()">Zaten hesabınız var mı? Giriş yapın</a>
            </div>
            <?php endif; ?>
        </header>

        <main>
            <?php if(isset($_GET['hata'])): ?>
                <div class="error">
                    <?php
                    switch($_GET['hata']) {
                        case 'gecersizistek':
                            echo "Geçersiz istek!";
                            break;
                        case 'urunbulunamadi':
                            echo "Ürün bulunamadı!";
                            break;
                        case 'urunzatensatildi':
                            echo "Bu ürün zaten satılmış!";
                            break;
                        case 'kendiurunu':
                            echo "Kendi ürününüzü satın alamazsınız!";
                            break;
                        case 'satiskaydi':
                            echo "Satış kaydı oluşturulurken bir hata oluştu!";
                            break;
                        case 'odemekaydi':
                            echo "Ödeme kaydı oluşturulurken bir hata oluştu!";
                            break;
                        case 'urunguncelleme':
                            echo "Ürün güncellenirken bir hata oluştu!";
                            break;
                        case 'islembasarisiz':
                            echo "İşlem başarısız oldu! Lütfen daha sonra tekrar deneyin.";
                            break;
                        default:
                            echo "Bir hata oluştu!";
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['durum']) && $_GET['durum'] == 'satinalindi'): ?>
                <div class="success">
                    Ürün başarıyla satın alındı!
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['uyeId'])): ?>
                <section class="urunler">
                    <h2>Satılık Ürünler</h2>
                    <?php
                    $urunler = $db->query("SELECT u.*, uy.uyeAd, uy.uyeSoyad FROM urunler u 
                                         JOIN uyeler uy ON u.uyeId = uy.uyeId 
                                         WHERE u.satildi = 0
                                         ORDER BY u.urunId DESC");
                    while($urun = $urunler->fetch(PDO::FETCH_ASSOC)):
                    ?>
                    <div class="urun-card">
                        <h3><?php echo htmlspecialchars($urun['urunAdi']); ?></h3>
                        <p class="fiyat"><?php echo number_format($urun['urunFiyat'], 2, ',', '.'); ?> TL</p>
                        <p class="satici">Satıcı: <?php echo htmlspecialchars($urun['uyeAd'] . ' ' . $urun['uyeSoyad']); ?></p>
                        <?php if($urun['uyeId'] != $_SESSION['uyeId']): ?>
                        <a href="satin-al.php?id=<?php echo $urun['urunId']; ?>" class="satin-al">Satın Al</a>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                </section>
            <?php else: ?>
                <section class="welcome">
                    <h2>İkinci El Eşyalarınızı Güvenle Alın ve Satın</h2>
                    <p>Hemen üye olun ve alışverişe başlayın!</p>
                </section>
            <?php endif; ?>
        </main>
    </div>

    <script>
    function toggleForms() {
        const loginForm = document.querySelector('.login-form');
        const registerForm = document.querySelector('.register-form');
        
        if(loginForm.style.display === 'none') {
            loginForm.style.display = 'block';
            registerForm.style.display = 'none';
        } else {
            loginForm.style.display = 'none';
            registerForm.style.display = 'block';
        }
    }
    </script>
</body>
</html> 