# Story Publisher

OpenCart 4 için Markdown tabanlı ürün yayınlama aracı.

Her ürün bir hikâyedir. Hikâyeyi tek bir Markdown dosyasına yazar, görselleri
ve indirilebilir dosyaları klasöre koyar ve içe aktarıcıyı çalıştırırsın.
OpenCart senin için güncellenir. HTML, PHP, SQL ya da OpenCart şablonlarını
asla düzenlemezsin.

Daha fazla belge:

- `docs/SPECIFICATION.md` — güncel özellik belirtimi
- `docs/ARCHITECTURE.md` — kod mimarisi (bakımı yapan için)
- `docs/MAINTENANCE.md` — kurulum, izinler, sorun giderme
- `docs/DECISIONS.md` — mimari kararlar ve gerekçeleri
- `docs/ROADMAP.md` — yol haritası
- `talimatlar.md` — tasarım felsefesi

## Bir ürün nasıl görünür

Her ürün tek bir klasördür:

Klasör adı, aynı zamanda ürünün **kodudur**. Açıklayıcı bir ad ver
(örneğin `servo-yatagi`); hem ürünün URL'i hem de kodu bu olur:

```
site/products/
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

`product.md` dosyasının en üstünde meta veriler, altında hikâye bulunur. Her
üst düzey `#` başlığı ürün sayfasında ayrı bir **sekme** olur; böylece
hikâyeyi okuyucunun dilinde yazabilirsin:

```markdown
---
name: Servo Yatağı
price: 149
category: 3D Modeller
image: kapak.jpg
summary: Robot kolunuzdaki esnemeyi bitiren sağlam bir servo yatağı.
status: enabled
---

# Sorun

...

# Hayal

![](stok-braket.jpg)
```

- `name` ve `price` zorunludur.
- `model` ürünün benzersiz kodudur ve aynı zamanda ürünü tanımlar: aynı
  modeli tekrar içe aktarmak kopya oluşturmaz, mevcut ürünü **günceller**.
  `model` yazmazsan **klasör adı** kod olarak kullanılır. Yani el ile kod
  uydurmana gerek yok; açıklayıcı bir klasör adı (`servo-yatagi`) hem URL
  hem kod olur. (Belirli bir kod biçimi istersen `model:` alanını yine de
  elle yazabilirsin.)
- `category` ürünün kategorisidir. Kategori OpenCart'ta varsa ürün ona
  bağlanır; **yoksa içe aktarıcı onu otomatik oluşturur** (üst düzey bir
  kategori olarak). Yani yeni bir kategori için OpenCart'a girmene gerek yok;
  sadece `category:` alanına yazman yeterli. Yazımına dikkat et — yanlış
  yazarsan yanlış adda yeni bir kategori oluşur.
- `image` ana ürün görselidir ve `images/` içinde bir dosya olmalıdır.
  OpenCart bu kapağı ürün sayfasının üstünde otomatik gösterir; hikâyenin
  içinde `![](kapak.jpg)` ile tekrar etmene gerek yok.
- `summary` bir-iki cümlelik düz metin özettir. Sekmelerin üstünde kısa bir
  giriş olarak görünür ve **kategori/arama listelerinde** OpenCart'ın
  gösterdiği metindir. Yazmazsan, listelerde sekme başlıkları yan yana
  yapışıp anlamsız görünür; en az bir tam cümle yaz.
- `status` değeri `enabled` ya da `disabled`'dır (varsayılan: `enabled`).
- Görselleri sade adlarıyla göster, örneğin `![](prototip.jpg)`. İçe
  aktarıcı bunları OpenCart'a kopyalar ve yolları düzeltir. Asla OpenCart
  yolu yazma.
- Büyük fotoğrafları elle küçültmene gerek yok. İçe aktarıcı, belirli bir
  genişlikten (`MAX_IMAGE_WIDTH`, varsayılan 600 piksel) büyük görselleri
  otomatik küçültür; en-boy oranı korunur, görsel kırpılmaz. `images/`
  içindeki asıl dosyalarına dokunulmaz.

## Kurulum

1. Yapılandırma dosyasını kopyala ve doldur:

   ```
   cp config.example.php config.php
   ```

   OpenCart veritabanı bilgilerini ve klasör yollarını `config.php` içine
   yaz. (`config.php` şifreni içerdiği için git tarafından yok sayılır.)

2. Gereksinimler: `mysqli` eklentisine sahip PHP 8 veya üzeri ve bir
   OpenCart 4 kurulumu.

## İçe aktarma

Tüm ürünleri içe aktar:

```
php import.php
```

Tek bir ürün klasörünü içe aktar:

```
php import.php servo-yatagi
```

İçe aktarıcı her ürün için bir satır ve kısa bir özet yazdırır. Bir ürün
başarısız olursa; **o üründeki tüm sorunları bir arada** listeler, hangi
dosyada olduğunu (`site/products/<klasör>/product.md`) söyler ve kalan ürünlerle
devam eder. Böylece bir hatayı düzeltip tekrar çalıştırıp bir sonrakini
bulmak yerine hepsini tek seferde görürsün.

Örnek çıktı:

```
OK    servo-yatagi (updated)
        note: Created category 'Kalıplar'.
FAIL  bozuk-urun
        2 problems:
          - Missing field: price
          - Invalid status 'belki' (use 'enabled' or 'disabled')
          Fix in site/products/bozuk-urun/product.md

5 created, 1 updated, 1 failed (7 total).
```

Kategori oluşturma gibi bilgilendirici notlar da ilgili ürünün altında
`note:` olarak görünür; hata değildir, işlem başarıyla tamamlanmıştır.

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

1. `site/products/` klasörünün içinde, ürün için yeni bir klasör aç. Klasör adı
   **İngilizce harf, rakam ve tire** olsun (Türkçe karakter veya boşluk yok).
   Örnek: `kablo-tutucu`. Bu ad hem ürünün adresi (URL) hem de kodu olur;
   ayrıca `model` yazmana gerek kalmaz.
2. O klasörün içinde `product.md` adında bir dosya oluştur. İçine mevcut bir
   ürünü (örneğin `site/products/servo-yatagi/product.md`) kopyalayıp üstünden
   gidebilirsin. Başlıktaki alanları doldur:

   ```markdown
   ---
   name: Kablo Tutucu
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

#### Bir görsel hangi sekmede görünür?

Bir görselin hangi sekmede çıkacağını **dosya adı değil, onu yazdığın yer**
belirler. `![](resim.jpg)` satırını hangi `#` başlığının altına yazarsan,
görsel o sekmede görünür:

```markdown
# Sorun

![](stok-braket.jpg)      ← "Sorun" sekmesinde çıkar

# Geliştirme

![](gelistirme.jpg)       ← "Geliştirme" sekmesinde çıkar
![](final.jpg)            ← o da "Geliştirme" sekmesinde (aynı sekmede iki görsel)
```

- Dosya adları serbesttir; yeter ki metindeki `![](ad)` ile `images/`
  içindeki dosya adı birebir aynı olsun.
- Bir sekmeye hiç görsel koymayabilir ya da birkaç tane koyabilirsin.
- Baştaki `image:` alanı ayrıdır: o, ürünün **kapak (ana) görselidir** ve
  sekmelerden bağımsızdır. O da `images/` içinde bir dosya olmalıdır.

#### Yan yana galeri

Birkaç görseli **alt alta, aralarında yazı olmadan** yazarsan, otomatik
olarak **yan yana bir galeri** olurlar. Tek görsel tam genişlikte kalır.
Ekstra bir işaret yazmana gerek yok:

```markdown
# Paylaşım

![](yaprak.jpg)
![](damla.jpg)
![](altigen.jpg)
```

Bu üç görsel ürün sayfasında yan yana bir ızgara olarak görünür (telefonda
ikili, geniş ekranda üçlü sıra). Aralarına bir cümle koyarsan galeri olmaz,
her biri ayrı ayrı gösterilir.

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
git add site/products/
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

## Proje düzeni

Aynı `product.md` içeriğinden **iki tamamen bağımsız çıktı** üretilir. İkisi de
**yalnızca içeriği** (`site/products/`) paylaşır; kodları ayrıdır. Bu bilinçli
bir tercih: pipeline kodu iki yerde tekrar eder ama her tarafın neye ihtiyacı
olduğu net görünür.

**OpenCart tarafı (kök dizin):**
```
import.php            OpenCart'a içe aktarma — giriş noktası
config.example.php    OpenCart ayarları (config.php olarak kopyala)
src/                  OpenCart tarafının kodu
    Scanner, ProductParser, Product, MarkdownRenderer,
    ImageOptimizer, ImportException   (pipeline)
    Publisher, OpenCartApi            (OpenCart'a özel)
lib/Parsedown.php     Markdown kütüphanesi (MIT)
```

**Statik site tarafı (`site/` — kendine yeten, izole):**
```
site/
    build.php             Statik siteyi üretir — giriş noktası
    src/                  Statik tarafın KENDİ kod kopyası
        Scanner, ProductParser, Product, MarkdownRenderer,
        ImageOptimizer, ImportException   (pipeline kopyası)
        SiteBuilder                        (HTML yazar)
    lib/Parsedown.php     Kendi Markdown kütüphanesi kopyası
    assets/               style.css + tabs.js (çerçevesiz)
    products/             Ürün klasörleri (product.md) — içerik burada
    output/               Üretilen site (git'e girmez)
```

`site/` klasörü tek başına kopyalanıp çalıştırılabilir: içinde kod, kütüphane,
görsel varlıklar ve **ürün içeriği** birlikte durur. Dosya sisteminde dışarı
uzanmaz.

**Ortak:**
```
products/  ->  içerik artık site/products altında. OpenCart tarafı da onu okur
              (config.php'de PRODUCTS_DIR = .../site/products). Tek kopya, çift yazım yok.
tests/run.php   Kök src/ pipeline'ının testleri
docs/           Belirtim, mimari, yol haritası, kararlar
```

**İki çıktı, tek içerik (`site/products`):**
- OpenCart mağazasına içe aktar: `php import.php`
- OpenCart'sız statik site üret: `php site/build.php` → `site/output/`

> Not: Pipeline (Scanner/Parser/Renderer/ImageOptimizer) hem `src/` hem
> `site/src/` içinde bulunur — kasıtlı tekrar. Bu ortak mantıkta bir değişiklik
> yaparsan **iki kopyaya da** uygula.

## Statik siteyi çalıştırma (Windows dahil)

`site/` klasörü kendine yeter; OpenCart, MySQL veya sunucu gerektirmez. Yeni bir
Windows makinesinde:

1. **PHP kur.** <https://windows.php.net/download> adresinden PHP 8.x zip'ini
   indir, örn. `C:\php`'ye çıkar ve `C:\php`'yi PATH'e ekle.
   `php.ini-development`'ı `php.ini` yap ve içinde `extension=gd` satırını aç
   (başındaki `;`'yi sil) — görsel küçültme bunu kullanır. MySQL gerekmez.
   Kontrol: `php -v`
2. **Projeyi al.** Git for Windows ile `git clone <depo>` **ya da** sadece
   `site` klasörünü kopyala.
3. **Üret.** Bir terminalde (PowerShell/CMD/Git Bash):
   ```
   cd site
   php build.php
   ```
4. **Aç.** `site\output\index.html`'i tarayıcıda aç, ya da hızlı bir sunucu:
   ```
   php -S localhost:8000 -t output
   ```
   sonra <http://localhost:8000>.

`output/` her çalıştırmada yeniden üretilir; barındırmak için (Netlify, GitHub
Pages, Cloudflare Pages) o klasörün içeriğini yüklersin.

## Testleri çalıştırma

```
php tests/run.php
```

Bu testler tarayıcıyı (scanner), ayrıştırıcıyı (parser) ve Markdown
dönüştürücüyü kapsar. Veritabanı tarafı gerçek bir OpenCart kurulumu
gerektirir ve bu test çalışmasına dahil değildir.
