<?php
session_start();
include 'baglan.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $sifre = $_POST['sifre'];

    $sorgu = $db->prepare("SELECT * FROM uyeler WHERE uyeMail = ?");
    $sorgu->execute([$email]);
    $uye = $sorgu->fetch(PDO::FETCH_ASSOC);

    if ($uye && $sifre === $uye['uyeSifre']) {
        $_SESSION['uyeId'] = $uye['uyeId'];
        $_SESSION['uyeAd'] = $uye['uyeAd'];
        $_SESSION['uyeSoyad'] = $uye['uyeSoyad'];
        
        header("Location: index.php");
        exit();
    } else {
        header("Location: index.php?hata=giris");
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
    <title>Giriş Yap - 2.El Mağaza</title>
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
            <h2>Giriş Yap</h2>
            <?php if(isset($hata)): ?>
                <div class="error"><?php echo $hata; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="email">E-posta:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="sifre">Şifre:</label>
                    <input type="password" id="sifre" name="sifre" required>
                </div>
                <button type="submit" name="giris" class="btn">Giriş Yap</button>
            </form>
            <p style="margin-top: 1rem; text-align: center;">
                Hesabınız yok mu? <a href="kayit.php">Kayıt Olun</a>
            </p>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 2.El Mağaza. Tüm hakları saklıdır.</p>
    </footer>
</body>
</html> 