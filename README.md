# 🎟️ Bilet Satın Alma Platformu

Bu proje, modern web teknolojilerini kullanarak **dinamik, veritabanı destekli ve çok kullanıcılı** bir otobüs bileti satış platformu geliştirmeyi amaçlamaktadır.

---

## 🎯 Görevin Amacı

Bu görevin amacı, modern web teknolojilerini kullanarak dinamik, veritabanı destekli ve çok kullanıcılı bir **otobüs bileti satış platformu** geliştirmektir.  
Bu proje ile öğrenciler:

- PHP dilinde yetkinlik kazanacak,  
- SQLite veritabanı mimarisi kurmayı ve yönetmeyi öğrenecek,  
- Kullanıcı rolleri ve yetkilendirme sistemleri oluşturacak,  
- Temel web güvenlik prensiplerini uygulayacaktır.  

---

## 🧩 Teknoloji ve Araçlar

| Bileşen | Açıklama |
|---------|-----------|
| **Programlama Dili** | PHP |
| **Arayüz** | HTML & CSS (isteğe bağlı olarak Bootstrap) |
| **Veritabanı** | SQLite |
| **Bağımlılık Yönetimi** | Composer |
| **PDF Oluşturma** | FPDF kütüphanesi |
| **Docker** | Paketleme ve dağıtım ortamı |

---

## 👥 Kullanıcı Rolleri ve Yetkiler

Platform, dört farklı kullanıcı rolünü destekler:

### 🔹 A. Ziyaretçi (Giriş Yapmamış Kullanıcı)
- Ana sayfada kalkış ve varış noktası seçerek seferleri listeleyebilir.  
- Sefer detaylarını görebilir ancak bilet satın alamaz.  
- “Bilet Satın Al” butonuna tıkladığında “Lütfen Giriş Yapın” uyarısı alır.

### 🔹 B. User (Yolcu)
- Sisteme kayıt olabilir ve giriş yapabilir.  
- Seferleri listeleyebilir, bilet satın alabilir ve iptal edebilir.  
- Satın alınan biletleri görüntüleyebilir, iptal edebilir, PDF olarak indirebilir.  
- Bilet iptali, kalkış saatine 1 saatten az süre kaldığında engellenir.  
- Başarılı iptal durumunda ücret hesabına iade edilir.  
- Kupon kodu kullanarak indirim uygulayabilir.

### 🔹 C. Firma Admin (Firma Yetkilisi)
- Sadece kendi firmasına ait seferleri yönetebilir.  
- Yeni sefer oluşturabilir, mevcutları düzenleyebilir veya silebilir.  
- CRUD (Create, Read, Update, Delete) işlemleri yapabilir.

### 🔹 D. Admin
- Sistemdeki en yetkili roldür.  
- Yeni otobüs firmaları oluşturabilir, düzenleyebilir veya silebilir.  
- Yeni “Firma Admin” kullanıcıları oluşturabilir ve firmalara atayabilir.  
- İndirim kuponları oluşturabilir, düzenleyebilir, silebilir.

---

## 🧱 Sayfa ve Yetki Mimarisi

| Sayfa / İşlev | Ziyaretçi | User | Firma Admin | Admin | Açıklama |
|----------------|-----------|------|--------------|--------|-----------|
| Ana Sayfa | ✅ | ✅ | ✅ | ✅ | Sefer arama ve listeleme formu |
| Giriş / Kayıt Ol | ✅ | ✅ | ✅ | ✅ | Kullanıcı giriş sistemi |
| Sefer Detayları | ✅ | ✅ | ✅ | ✅ | Sefer bilgileri görüntüleme |
| Bilet Satın Alma | ❌ | ✅ | ❌ | ❌ | Kredi ile bilet alımı ve kupon uygulaması |
| Bilet İptal Etme | ❌ | ✅ | ✅ | ❌ | Son 1 saat kuralı ile bilet iptali |
| Hesabım / Biletler | ❌ | ✅ | ✅ | ❌ | Profil, kredi, geçmiş biletler, PDF indirme |
| Firma Admin Paneli | ❌ | ❌ | ✅ | ❌ | Sefer yönetimi (CRUD) |
| Admin Paneli | ❌ | ❌ | ❌ | ✅ | Firma, Firma Admin ve kupon yönetimi |

---

## 🛠️ Geliştirme Adımları

1. **Veritabanı Kurulumu:**  
   SQLite veritabanı dosyası (`bilet_platformu.sqlite`) ve tablolar oluşturuldu.

2. **Kullanıcı Sistemi:**  
   - Kayıt olma, giriş yapma ve çıkış işlemleri tamamlandı.  
   - Session yönetimi ile kullanıcı oturumları kontrol ediliyor.

3. **Ana Sayfa ve Sefer Listeleme:**  
   - Ziyaretçiler kalkış-varış bilgisiyle seferleri arayabilir.  
   - Tarih filtreleme desteklenir.

4. **Rol Yönetimi:**  
   - Giriş yapan kullanıcının rolüne göre menü ve buton görünürlükleri dinamik olarak belirlenir.

5. **Firma Admin Paneli:**  
   - Firma Admin kullanıcıları kendi firmalarına ait seferleri CRUD işlemleriyle yönetebilir.

6. **Admin Paneli:**  
   - Admin kullanıcıları firma, Firma Admin ve kupon yönetimini yapabilir.

7. **Bilet Satın Alma ve Koltuk Seçimi:**  
   - Dolu koltuklar devre dışı (disabled) gösterilir.  
   - Kupon kodu girildiğinde indirim uygulanır.

8. **Bilet İptal Etme:**  
   - Seferin kalkış saatine 1 saatten az kaldıysa iptal engellenir.  
   - Başarılı iptalde ücret iade edilir.

9. **PDF Bilet Üretimi:**  
   - Satın alınan biletler FPDF ile PDF formatında indirilebilir.

---

## 🧮 Veritabanı

Veritabanı: `storage/bilet_platformu.sqlite`  
SQLite tabanlı olup aşağıdaki örnek tabloları içerir:

- `users`
- `companies`
- `trips`
- `tickets`
- `coupons`
- `roles`

> Veritabanı dosyası küçük olduğu sürece `storage/` klasörüne eklenebilir.  
> Ancak büyük veya hassas içerik içeren dosyalar `.gitignore` ile hariç tutulmalıdır.

---

## ⚙️ Kurulum ve Çalıştırma

### 1️⃣ Bağımlılıkların yüklenmesi

```bash
composer install
