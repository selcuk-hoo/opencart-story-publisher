# Story Publisher

Markdown-based product publisher for OpenCart 4.

Every product is a story. You write the story in one Markdown file, drop in
your images and downloadable files, and run the importer. OpenCart is updated
for you. You never edit HTML, PHP, SQL, or OpenCart templates.

See `docs/SPECIFICATION.md` for the full Version 0.1 specification and
`talimatlar.md` for the design philosophy.

## How a product looks

Each product is a single folder:

```
products/
    servo-yatagi/
        product.md
        images/
            kapak.jpg
            stok-braket.jpg
            final.jpg
        downloads/
            servo-yatagi.stl
            servo-yatagi.step
```

`product.md` has metadata at the top and the story below. Each top-level
`#` heading becomes a tab on the product page, so you can write the story
in the reader's own language:

```markdown
---
name: Servo Yatağı
model: SRV-001
price: 149
category: 3D Modeller
image: kapak.jpg
status: enabled
---

# Sorun

...

# Hayal

![](stok-braket.jpg)
```

- `name`, `model`, and `price` are required. `model` also identifies the
  product: importing the same model again **updates** it instead of creating
  a duplicate.
- `category` must already exist in OpenCart (Version 0.1 does not create
  categories).
- `image` is the main product image and must be a file in `images/`.
- `status` is `enabled` or `disabled` (default: `enabled`).
- Reference images with plain names, e.g. `![](prototype.jpg)`. The importer
  copies them into OpenCart and fixes the paths. Never write OpenCart paths.

## Setup

1. Copy the configuration file and fill it in:

   ```
   cp config.example.php config.php
   ```

   Put your OpenCart database details and folder paths in `config.php`.
   (`config.php` is ignored by git because it holds your password.)

2. Requirements: PHP 8 or newer with the `mysqli` extension, and an
   OpenCart 4 installation.

## Importing

Import every product:

```
php import.php
```

Import a single product folder:

```
php import.php servo-yatagi
```

The importer prints one line per product and a short summary. If a product
fails, it says which product, what went wrong, and how to fix it, and then
continues with the rest.

## Windows'ta ürün ekleme (içerik editörü için)

Bu bölüm, ürünleri ekleyecek kişi içindir. Programcı olmana gerek yok.
İşin sadece **yazmak**: bir Markdown dosyası, birkaç görsel ve indirilebilir
dosya. PHP, veritabanı, sunucu — hiçbiriyle uğraşmıyorsun.

OpenCart mağazası Linux sunucusunda çalışıyor. Sen Windows'ta içerik
hazırlıyorsun ve git ile gönderiyorsun; içe aktarma (import) sunucuda
yapılıyor. Yani senin akışın şu:

**Yaz → Görselleri koy → Dosyaları koy → Gönder (git push).** Gerisi
sunucuda hallolur.

### 1. Tek seferlik kurulum

1. **Git for Windows** kur: <https://git-scm.com/download/win>. Kurulum
   sırasında her şeyi varsayılan bırakabilirsin. Bu sana bir de "Git Bash"
   adında bir terminal verir; aşağıdaki komutları orada çalıştıracaksın.
2. Bir **metin editörü** kur. Öneri: **VS Code** (<https://code.visualstudio.com>)
   ya da not tutmayı seviyorsan **Obsidian**. Windows Not Defteri de olur ama
   aşağıdaki tuzaklara dikkat et.
3. Projeyi bilgisayarına indir. Git Bash'i aç ve şunu yaz (adresi abin verecek):

   ```
   git clone <depo-adresi>
   cd opencart-story-publisher
   ```

### 2. Yeni bir ürün ekleme

1. `products/` klasörünün içinde, ürün için yeni bir klasör aç. Klasör adı
   **İngilizce harf, rakam ve tire** olsun (Türkçe karakter veya boşluk yok).
   Örnek: `kablo-tutucu`.
2. O klasörün içinde `product.md` adında bir dosya oluştur. İçine mevcut bir
   ürünü (örneğin `products/servo-yatagi/product.md`) kopyalayıp üstünden
   gidebilirsin. Başlıktaki alanları doldur:

   ```markdown
   ---
   name: Kablo Tutucu
   model: KBL-001
   price: 49
   category: 3D Modeller
   image: kapak.jpg
   status: enabled
   ---

   # Sorun

   Hikâyeni buraya yaz...

   # Hayal

   ![](kapak.jpg)
   ```

   Her `#` başlığı ürün sayfasında ayrı bir **sekme** olur. İstediğin kadar
   sekme ekleyebilirsin; başlığı Türkçe yazman yeterli.
3. Aynı ürün klasörünün içinde `images/` ve `downloads/` klasörlerini oluştur.
   - Görselleri `images/` içine kopyala. Metinde `![](kapak.jpg)` şeklinde,
     sadece dosya adıyla göster — OpenCart yolu yazma.
   - STL, STEP, PDF gibi indirilebilir dosyaları `downloads/` içine koy.

### 3. Windows'a özel dikkat edilecekler

- **Dosya uzantısı tuzağı:** Windows dosya uzantılarını gizler. Not
  Defteri'yle kaydedince dosyan `product.md` yerine gizliden `product.md.txt`
  olabilir. Çözüm: Dosya Gezgini'nde **Görünüm → Dosya adı uzantıları**
  kutusunu işaretle, ya da baştan **VS Code** kullan (bu sorun hiç olmaz).
- **Türkçe karakter kodlaması:** Dosyayı **UTF-8** olarak kaydet ki ç, ş, ğ,
  ı gibi harfler bozulmasın. VS Code varsayılan olarak UTF-8 kaydeder; modern
  Not Defteri de öyle. (Kaygılanma — `product.md` *içeriğinde* Türkçe kullanmak
  serbest ve sorunsuz.)
- **Dosya ve klasör adları:** Yalnızca dosya/klasör *isimlerinde* Türkçe
  karakter ve boşluk kullanma (`miknatis-yuva.jpg` iyi, `mıknatıs yuvası.jpg`
  kötü). İçerik Türkçe olabilir, sadece dosya adları sade İngilizce olsun.
- **Satır sonları (CRLF):** Hiç dert etme. İçe aktarıcı Windows ve Linux
  satır sonlarının ikisini de anlıyor.

### 4. Yayınlama (git ile gönderme)

Ürünü hazırladıktan sonra Git Bash'te, proje klasörünün içinde:

```
git pull
git add products/
git commit -m "Yeni ürün: Kablo Tutucu"
git push
```

`git pull`, başkasının yaptığı değişiklikleri önce sana çeker; `git push`
seninkileri gönderir. Gönderdikten sonra sunucuda `php import.php` çalışınca
ürünün mağazada yayınlanır (bu adımı abin çalıştırır ya da otomatikleştirir).

### İsteğe bağlı: içe aktarmayı Windows'ta çalıştırmak

Eğer OpenCart senin Windows makinende de kuruluysa (örneğin XAMPP ile), içe
aktarmayı kendin de çalıştırabilirsin. Linux'tan tek farkı `config.php`:

- **PHP:** XAMPP ile birlikte gelir. `php` komutunu tanıması için XAMPP'ın
  `php` klasörünü PATH'e ekle. `mysqli` ve `gd` eklentileri açık olmalı
  (XAMPP'ta ikisi de varsayılan açıktır).
- **Yollar:** `config.php` içinde Windows yollarını **ileri eğik çizgiyle**
  yaz, PHP bunu kabul eder:

  ```php
  define('OPENCART_IMAGE_DIR', 'C:/xampp/htdocs/opencart/image');
  define('OPENCART_DOWNLOAD_DIR', 'C:/xampp/htdocs/opencart/system/storage/download');
  ```

- **Çalıştırma:** Linux'takiyle aynı — `php import.php`.

## Project layout

```
import.php            Command line entry point
config.example.php    Copy to config.php and fill in
src/                  The pipeline
    Scanner.php           Finds product folders
    ProductParser.php     Reads product.md and validates it
    MarkdownRenderer.php  Turns the story into HTML
    Publisher.php         Runs the import, copies files, reports
    OpenCartApi.php       The only class that touches OpenCart's database
    Product.php           Plain data holder
    ImportException.php   Human-readable import errors
lib/Parsedown.php     Markdown library (single file, MIT)
products/             Your product folders
tests/run.php         Tests for the non-database parts
docs/                 Specification, architecture, roadmap, decisions
```

## Running the tests

```
php tests/run.php
```

These cover the scanner, the parser, and the Markdown renderer. The database
side needs a real OpenCart installation and is not part of this test run.
