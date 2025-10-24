🚌 Bilet Satın Alma Platformu

Bu proje, kullanıcıların otobüs seferleri arasında arama yapabildiği, koltuk seçimi yaparak bilet satın alabildiği ve geçmiş biletlerini görüntüleyebildiği bir web platformudur.

🛠️ Kullanılan Teknolojiler

Backend: PHP (PDO ile veritabanı bağlantısı)

Veritabanı: SQLite

PDF İşlemleri: FPDF (bilet çıktısı oluşturmak için)

Paketleme ve Dağıtım: Docker (Container yapısı)

🎯 Projenin Amacı

Bu platformun temel amacı, kullanıcılara aşağıdaki işlemleri kolayca yapabilme imkânı sunmaktır:

Üye olup giriş yaptıktan sonra otobüs bileti satın alma

Mevcut kullanıcı bakiyesini görüntüleme

Satın alma sırasında kupon kodu kullanarak indirim uygulama

Daha önce satın alınmış olan biletleri listeleme ve PDF olarak indirme

🚍 Temel Özellikler
Özellik	Açıklama
Sefer Arama	Kalkış, varış ve tarih bilgisine göre uygun seferlerin listelenmesi
Koltuk Seçimi	Otobüsün tekli/ikili koltuk düzenine göre görsel koltuk seçimi
Bakiye Kontrolü	Kullanıcının hesabındaki mevcut bakiyeyi kontrol etme
İndirim Kuponu	Geçerli kupon kodlarıyla bilet fiyatına indirim uygulama
Bilet Listeleme	Kullanıcının satın aldığı biletleri listeleme
PDF Oluşturma (FPDF)	Satın alınan biletin PDF çıktısını oluşturma
Kullanıcı Yönetimi	Güvenli giriş (login) ve kayıt (register) sistemi
⚙️ Kurulum ve Çalıştırma

Bu proje bir Docker container yapısıyla paketlenmiştir.
Projeyi çalıştırmak için şu adımları izleyin 👇

Proje dosyalarını yerel makinenize indirin veya klonlayın:

git clone https://github.com/melisakumral/bilet-satin-alma.git
cd bilet-satin-alma


Gerekli bağımlılıkları yükleyin
(Bu işlem vendor klasörünü otomatik olarak oluşturur.)

composer install


Docker’ın kurulu olduğundan emin olun.
Ardından container’ları oluşturmak için:

docker-compose up -d --build


Tarayıcıdan erişim sağlayın:

http://localhost:[PortNumarası]


Port numarası docker-compose.yml dosyasında belirtilmiştir.

🗄️ Veritabanı Yapısı

Proje, SQLite veritabanı kullanmaktadır.
PDO bağlantısı ile güvenli veri işlemleri yapılır.

Veritabanı dosyası: storage/bilet_platformu.sqlite

Temel tablolar:

users → Kullanıcı bilgileri

trips → Sefer bilgileri

tickets → Satın alınan biletler

coupons → İndirim kuponları

💡 Not: storage klasörü GitHub deposuna dahil edilmemiştir.
Gerekirse örnek bir veritabanı dosyası (bilet_platformu.sqlite) manuel olarak eklenebilir.

📦 Vendor Klasörü Hakkında

vendor klasörü, Composer tarafından otomatik oluşturulan kütüphaneleri içerir.
Bu klasör GitHub’a yüklenmemiştir çünkü herkes şu komutla kendinde oluşturabilir:

composer install


Bu komut, composer.json ve composer.lock dosyalarına göre tüm bağımlılıkları yükler.

🧾 Örnek PDF Oluşturma (FPDF)

Projede, kullanıcıların satın aldıkları biletlerin çıktısını alabilmesi için FPDF kütüphanesi kullanılmaktadır.
Örnek PDF dosyası oluşturmak için proje içerisinde test_pdf.php dosyası kullanılabilir.
