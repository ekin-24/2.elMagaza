# 2. El Mağaza - Cloud-Native Kubernetes & CI/CD Pipeline

Bu proje, modern bulut bilişim (Cloud-Native) mimarisi standartlarına uygun olarak tasarlanmış, konteynerleştirilmiş ve **Google Kubernetes Engine (GKE)** üzerinde yüksek erişilebilirlik ve otomatik ölçeklenebilirlik ile çalışan bir **2. El Alım-Satım Mağazası** web uygulamasıdır.

Proje, manuel altyapı yönetimini tamamen ortadan kaldırarak GitOps yaklaşımıyla **Google Cloud Build** ve GitHub entegrasyonu üzerinden tam otomatize edilmiş bir **CI/CD (Sürekli Entegrasyon / Sürekli Dağıtım)** hattına sahiptir.

---

## Mimari Yapı ve Tasarım Prensipleri

Uygulama, yüksek erişilebilirlik (High Availability), veri kalıcılığı (Data Persistence) ve katı ağ güvenliği (Network Isolation) kriterlerini karşılayacak şekilde mikro-mimari prensipleriyle kurgulanmıştır:

1. **Uygulama Katmanı (Frontend/Backend):** PHP tabanlı web uygulaması, yük dengelemesi ve kesintisiz hizmet için mimari seviyede **2 kopya (replica)** halinde dağıtılır.
2. **Veri Katmanı (Database):** Veritabanı yönetim sistemi olarak **MySQL 8.0** tercih edilmiştir. Pod'ların geçici (ephemeral) doğasından etkilenmemek amacıyla veriler bulut tabanlı blok depolama ünitelerine bağlanmıştır.
3. **Güvenlik Katmanı (Ağ İzolasyonu):** Veritabanı katmanı dış dünyaya tamamen kapatılmış, küme içi siber güvenliği en üst düzeye çıkarmak adına **Sıfır Güven (Zero-Trust)** mimarisine uygun ağ politikaları (NetworkPolicy) tanımlanmıştır.

---

## Proje Klasör Yapısı

├── k8s/                            # Kubernetes Manifest (Konfigürasyon) Dosyaları
│   ├── web.yaml                    # Web Deployment ve LoadBalancer Servisi
│   ├── mysql.yaml                  # MySQL Deployment ve İç Servis Tanımı
│   ├── pvc.yaml                    # Kalıcı Disk Talep Belgesi (PersistentVolumeClaim)
│   └── networkpolicy.yaml          # Katı Ağ Güvenlik Duvarı Politikaları
├── cloudbuild.yaml                 # Google Cloud Build CI/CD Konfigürasyonu
├── index.php                       # Uygulama Ana Sayfası (Backend & Frontend)
└── README.md                       # Proje Ana Dokümantasyonu

---

## CI/CD Otomasyon Süreci ve GitOps İş Akışı

Sistem tamamen kod tabanlı otomasyon (GitOps) prensiplerine göre çalışmaktadır. Kod üzerinde değişiklik yapılıp GitHub'a `git push` yapıldığında şu süreçler işletilir:

1. **Docker Build:** GitHub reposuna yeni kod ulaştığı an Cloud Build Tetikleyicisi otomatik olarak devreye girer. Güncel kaynak kodlarla yeni bir Docker imajı inşa edilir.
2. **Docker Push:** Derlenen optimize imaj, Google Container Registry (GCR) üzerindeki güvenli depolama alanına gönderilir.
3. **Kubernetes Deployment:** Sistem, `kubectl apply -f k8s/` komutunu otomatik çalıştırarak güncel bildirimleri GKE cluster'ına uygular ve kesintisiz (Rolling Update) geçiş sağlar.