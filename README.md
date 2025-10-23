# Bilet Satın Alma Platformu

Bu proje, kullanıcıların otobüs seferleri arasında arama yapabildiği, koltuk seçimiyle bilet satın alabildiği bir web platformudur.

## Proje Teknolojileri

* **Backend:** PHP (PDO ile veritabanı bağlantısı)
* **Veritabanı:** SQLite
* **Paketleme ve Dağıtım:** Docker (Container yapısı)

## Projenin Amacı

Bu platformun temel amacı, kullanıcılara aşağıdaki imkanları sunmaktır:

1.  Üye olup giriş yaptıktan sonra otobüs bileti satın alma.
2.  Mevcut kullanıcı bakiyesini görüntüleme.
3.  Satın alma sırasında kupon kodu kullanarak indirim uygulama.
4.  Daha önce satın alınmış olan biletleri listeleyebilme.

## Temel Özellikler

Platform aşağıdaki temel işlevleri sunmaktadır:

* **Sefer Arama:** Kalkış noktası, varış noktası ve tarih kriterlerine göre sefer arama yeteneği.
* **Koltuk Seçimi:** Otobüsün tekli ve ikili koltuk düzenine uygun görsel koltuk seçimi.
* **Bakiye Kontrolü:** Kullanıcının mevcut bakiyesini kontrol etme ve satın alma işleminde kullanma.
* **İndirim Kuponu:** Geçerli kupon kodları ile bilet fiyatına indirim uygulama desteği.
* **Bilet Listeleme:** Kullanıcıya ait satın alınmış tüm biletlerin listelenmesi.
* **Kullanıcı Yönetimi:** Güvenli kullanıcı girişi (login) ve kayıt (register) sistemi.

## Kurulum ve Çalıştırma

Bu projenin bir Docker container yapısıyla paketlendiği varsayılmıştır. Projeyi çalıştırmak için temel adımlar şunlardır:

1.  Proje dosyalarını yerel makinenize indirin.
2.  Sisteminize Docker ve Docker Compose'un kurulu olduğundan emin olun.
3.  Proje ana dizininde aşağıdaki komutu çalıştırarak Docker container'larını oluşturun ve başlatın:

    ```bash
    docker-compose up -d --build
    ```

4.  Container'lar başlatıldıktan sonra tarayıcınızdan platforma erişim sağlayabilirsiniz (Genellikle `http://localhost:[PortNumarası]` üzerinden). Kullanılan port numarası `docker-compose.yml` dosyasında belirtilmiştir.

## Veritabanı Yapısı

Proje, SQLite veritabanını kullanmaktadır. PDO bağlantısı ile işlemler güvenli bir şekilde gerçekleştirilmektedir. Veritabanı şemasında kullanıcılar, seferler, biletler ve kuponlar gibi temel tablolar yer almaktadır.
