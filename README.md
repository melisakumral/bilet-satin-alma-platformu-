# 🚌 Bilet Satın Alma Platformu

Bu proje, kullanıcıların **otobüs seferleri arasında arama yapabildiği**, **koltuk seçimi yaparak bilet satın alabildiği** ve **geçmiş biletlerini görüntüleyebildiği** bir web platformudur.

---

## 🛠️ Kullanılan Teknolojiler

- **Backend:** PHP (PDO ile veritabanı bağlantısı)  
- **Veritabanı:** SQLite  
- **PDF İşlemleri:** FPDF (bilet çıktısı oluşturmak için)  
- **Paketleme ve Dağıtım:** Docker (Container yapısı)  

---

## 🎯 Projenin Amacı

Bu platformun temel amacı, kullanıcılara aşağıdaki işlemleri kolayca yapabilme imkânı sunmaktır:

- Üye olup giriş yaptıktan sonra otobüs bileti satın alma  
- Mevcut kullanıcı bakiyesini görüntüleme  
- Satın alma sırasında kupon kodu kullanarak indirim uygulama  
- Daha önce satın alınmış olan biletleri listeleme ve PDF olarak indirme  

---

## 🚍 Temel Özellikler

| Özellik | Açıklama |
|----------|-----------|
| **Sefer Arama** | Kalkış, varış ve tarih bilgisine göre uygun seferlerin listelenmesi |
| **Koltuk Seçimi** | Otobüsün tekli/ikili koltuk düzenine göre görsel koltuk seçimi |
| **Bakiye Kontrolü** | Kullanıcının hesabındaki mevcut bakiyeyi kontrol etme |
| **İndirim Kuponu** | Geçerli kupon kodlarıyla bilet fiyatına indirim uygulama |
| **Bilet Listeleme** | Kullanıcının satın aldığı biletleri listeleme |
| **PDF Oluşturma (FPDF)** | Satın alınan biletin PDF çıktısını oluşturma |
| **Kullanıcı Yönetimi** | Güvenli giriş (login) ve kayıt (register) sistemi |

---

## ⚙️ Kurulum ve Çalıştırma

Bu proje bir **Docker container** yapısıyla paketlenmiştir.  
Projeyi çalıştırmak için aşağıdaki adımları izleyin 👇  

### 1️⃣ Projeyi klonlayın
```bash
git clone https://github.com/melisakumral/bilet-satin-alma.git
cd bilet-satin-alma
