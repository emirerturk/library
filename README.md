# library
Kütüphane Yönetim Sistemi API

Basit bir RESTful API projesidir. Bu sistemde kitap, yazar ve kategori işlemleri yapılabilir. Vanilla PHP(8.4) ve MySQL teknolojileri kullanılmıştır

## Kurulum Talimatları
- PHP 8.0 veya üzeri
- MySQL veya PostreSQL
- XAMPP
- Postman veya benzeri bir API test aracı

## Adımlar
1. Projeyi klonlayin veya indirin: git clone https://github.com/emirerturk/library.git

2. library_db isminde bir veritabani oluşturun
 <img width="719" alt="image" src="https://github.com/user-attachments/assets/46be9c5e-5c00-4294-ab73-40b601ab0444" /> <br>
3. Dilerseniz proje dosyaları içerisinde bulunan .sql uzantili schema ve sample_data dosyalarını sırasıyla içe aktar yoluyla ekleyin
    ya da
    SQL sekmesinden sql scriptlerini çalıştırarak gerekli tabloları ve örnek verileri yükleyin.
   
4. api/Database.php dosyasındaki veritabanı bağlantı bilgilerini güncelleyin:
  $host = 'localhost';
  $dbName = 'library_db';
  $username = 'root';
  $password = '';

5. Apache sunucusunu başlatın ve http://localhost/projects/library/api yolunu takip edin.

API Endpoint Listesi

Books
  GET /api/books - Tüm kitapları listele (pagination destekli)
  GET /api/books/search?q=keyword - Başlık veya ISBN'e göre kitap ara
  GET /api/books/{id} - ID'ye göre kitap getir
  POST /api/books/{id} - Kitap Güncelle
  DELETE /api/books/{id} - Kitap sil

Authors
  GET /api/authors - Tüm yazarları listele
  POST /api/authors - Yeni yazar ekle
  GET /api/authors/{id}/books - Belirli yazarın kitaplarını getir

Categories
  GET /api/categories - Tüm kategorileri listele
  POST /api/categories - Yeni kategori ekle

  Dilerseniz test için oluşturmuş olduğum postman collection üzerinden ilerleme sağlayabilirsiniz : https://.postman.co/workspace/My-Workspace~99ed3375-a927-4fcd-8358-0e9088c2e575/collection/38817488-a4b2f7e5-6974-48d5-a2fc-0598ee25e02d?action=share&creator=38817488
