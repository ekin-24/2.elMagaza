<?php
session_start();
include 'baglan.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ad = $_POST['ad'];
    $soyad = $_POST['soyad'];
    $telefon = $_POST['telefon'];
    $email = $_POST['email'];
    $adres = $_POST['adres'];
    $sifre = $_POST['sifre'];

    try {
        $sorgu = $db->prepare("CALL uyeEkle(?, ?, ?, ?, ?, ?)");
        $sorgu->execute([$ad, $soyad, $telefon, $email, $adres, $sifre]);

        $uyeId = $db->lastInsertId();
        $_SESSION['uyeId'] = $uyeId;
        $_SESSION['uyeAd'] = $ad;
        $_SESSION['uyeSoyad'] = $soyad;

        header("Location: index.php?durum=basarili");
        exit();
    } catch(PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry error
            header("Location: index.php?hata=mevcut");
        } else {
            header("Location: index.php?hata=kayit");
        }
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - 2.El Mağaza</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">2.El Mağaza</div>
            <ul>
                <li><a href="index.php">Ana Sayfa</a></li>
                <li><a href="giris.php">Giriş Yap</a></li>
                <li><a href="kayit.php">Kayıt Ol</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="form-container">
            <h2>Kayıt Ol</h2>
            <?php if(isset($hata)): ?>
                <div class="error"><?php echo $hata; ?></div>
            <?php endif; ?>
            <?php if(isset($basari)): ?>
                <div class="success"><?php echo $basari; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="ad">Ad:</label>
                    <input type="text" id="ad" name="ad" required>
                </div>
                <div class="form-group">
                    <label for="soyad">Soyad:</label>
                    <input type="text" id="soyad" name="soyad" required>
                </div>
                <div class="form-group">
                    <label for="tel">Telefon:</label>
                    <input type="tel" id="tel" name="tel" required>
                </div>
                <div class="form-group">
                    <label for="email">E-posta:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="adres">Adres:</label>
                    <textarea id="adres" name="adres" required></textarea>
                </div>
                <div class="form-group">
                    <label for="sifre">Şifre:</label>
                    <input type="password" id="sifre" name="sifre" required>
                </div>
                <button type="submit" name="kayit" class="btn">Kayıt Ol</button>
            </form>
            <p style="margin-top: 1rem; text-align: center;">
                Zaten hesabınız var mı? <a href="giris.php">Giriş Yapın</a>
            </p>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 2.El Mağaza. Tüm hakları saklıdır.</p>
    </footer>
</body>
</html> 