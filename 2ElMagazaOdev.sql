CREATE DATABASE IF NOT EXISTS 2elMagaza;
USE 2elMagaza;

CREATE TABLE IF NOT EXISTS uyeler (
uyeId INT PRIMARY KEY AUTO_INCREMENT,
uyeAd VARCHAR(50) NOT NULL,
uyeSoyad VARCHAR(50) NOT NULL,
uyeTel VARCHAR(15) NOT NULL UNIQUE,
uyeMail VARCHAR(100) NOT NULL UNIQUE,
uyeAdres TEXT NOT NULL,
uyeSifre VARCHAR(100) NOT NULL,
kayitTarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS urunler (
urunId INT PRIMARY KEY AUTO_INCREMENT,
urunAdi VARCHAR(100) NOT NULL,
urunFiyat DECIMAL(10,2) NOT NULL,
uyeId INT NOT NULL,
satildi TINYINT(1) DEFAULT 0,
eklemeTarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (uyeId) REFERENCES uyeler(uyeId)
);

CREATE TABLE IF NOT EXISTS satislar (
satisId INT PRIMARY KEY AUTO_INCREMENT,
urunId INT NOT NULL,
saticiId INT NOT NULL,
aliciId INT NOT NULL,
satisTarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (urunId) REFERENCES urunler(urunId),
FOREIGN KEY (saticiId) REFERENCES uyeler(uyeId),
FOREIGN KEY (aliciId) REFERENCES uyeler(uyeId)
);

CREATE TABLE IF NOT EXISTS odemeler (
odemeId INT PRIMARY KEY AUTO_INCREMENT,
satisId INT NOT NULL,
odemeTutari DECIMAL(10,2) NOT NULL,
odemeTarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (satisId) REFERENCES satislar(satisId)
);

CREATE TABLE IF NOT EXISTS urun_log (
logId INT PRIMARY KEY AUTO_INCREMENT,
urunId INT NOT NULL,
islem VARCHAR(20) NOT NULL,
eskiFiyat DECIMAL(10,2),
yeniFiyat DECIMAL(10,2),
islemTarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (urunId) REFERENCES urunler(urunId)
);



DELIMITER //
CREATE FUNCTION kdvliFiyat(fiyat DECIMAL(10,2))
RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
RETURN fiyat * 1.18;
END //
DELIMITER ;


DELIMITER //
CREATE PROCEDURE uyeEkle(
IN p_ad VARCHAR(50),
IN p_soyad VARCHAR(50),
IN p_tel VARCHAR(15),
IN p_mail VARCHAR(100),
IN p_adres TEXT,
IN p_sifre VARCHAR(100)
)
BEGIN
INSERT INTO uyeler (uyeAd, uyeSoyad, uyeTel, uyeMail, uyeAdres, uyeSifre)
VALUES (p_ad, p_soyad, p_tel, p_mail, p_adres, p_sifre);
END //
DELIMITER ;


DELIMITER //
CREATE PROCEDURE urunFiyatGuncelle(
IN p_urunId INT,
IN p_yeniFiyat DECIMAL(10,2)
)
BEGIN
DECLARE v_eskiFiyat DECIMAL(10,2);
SELECT urunFiyat INTO v_eskiFiyat
FROM urunler
WHERE urunId = p_urunId;
UPDATE urunler
SET urunFiyat = p_yeniFiyat
WHERE urunId = p_urunId;
INSERT INTO urun_log (urunId, islem, eskiFiyat, yeniFiyat)
VALUES (p_urunId, 'GUNCELLEME', v_eskiFiyat, p_yeniFiyat);
END //
DELIMITER ;


DELIMITER //
CREATE PROCEDURE urunSil(IN p_urunId INT)
BEGIN
DECLARE v_fiyat DECIMAL(10,2);
SELECT urunFiyat INTO v_fiyat
FROM urunler
WHERE urunId = p_urunId;
DELETE FROM urunler
WHERE urunId = p_urunId;
INSERT INTO urun_log (urunId, islem, eskiFiyat, yeniFiyat)
VALUES (p_urunId, 'SILME', v_fiyat, NULL);
END //
DELIMITER ;


DELIMITER //
CREATE TRIGGER urunSilLog
BEFORE DELETE ON urunler
FOR EACH ROW
BEGIN
INSERT INTO urun_log (urunId, islem, eskiFiyat, yeniFiyat)
VALUES (OLD.urunId, 'SILME', OLD.urunFiyat, NULL);
END //
DELIMITER ;


DELIMITER //
CREATE TRIGGER odemeSatildiGuncelle
AFTER INSERT ON odemeler
FOR EACH ROW
BEGIN
UPDATE urunler u
JOIN satislar s ON u.urunId = s.urunId
SET u.satildi = 1
WHERE s.satisId = NEW.satisId;
END //
DELIMITER ; 