# Story Publisher

OpenCart 4 için Markdown tabanlı ürün yayınlama aracı.

Her ürün bir hikâyedir. Hikâyeyi tek bir Markdown dosyasına yazar, görselleri
ve indirilebilir dosyaları klasöre koyar ve içe aktarıcıyı çalıştırırsın.
OpenCart senin için güncellenir. HTML, PHP, SQL ya da OpenCart şablonlarını
asla düzenlemezsin.

Tam Sürüm 0.1 belirtimi için `docs/SPECIFICATION.md`, tasarım felsefesi için
`talimatlar.md` dosyalarına bakabilirsin.

## Bir ürün nasıl görünür

Her ürün tek bir klasördür:

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

`product.md` dosyasının en üstünde meta veriler, altında hikâye bulunur. Her
üst düzey `#` başlığı ürün sayfasında ayrı bir **sekme** olur; böylece
hikâyeyi okuyucunun dilinde yazabilirsin:

```markdown
---
name: Servo Yatağı
model: SRV-001
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

- `name`, `model` ve `price` zorunludur. `model` aynı zamanda ürünü
  tanımlar: aynı modeli tekrar içe aktarmak yeni bir kopya oluşturmaz,
  mevcut ürünü **günceller**.
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
dosyada olduğunu (`products/<klasör>/product.md`) söyler ve kalan ürünlerle
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
          Fix in products/bozuk-urun/product.md

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

## Proje düzeni

```
import.php            Komut satırı giriş noktası
config.example.php    config.php olarak kopyala ve doldur
src/                  İşlem hattı (pipeline)
    Scanner.php           Ürün klasörlerini bulur
    ProductParser.php     product.md dosyasını okur ve doğrular
    MarkdownRenderer.php  Hikâyeyi HTML'e (ve sekmelere) çevirir
    ImageOptimizer.php    Büyük görselleri içe aktarırken küçültür
    Publisher.php         İçe aktarmayı yürütür, dosyaları kopyalar, rapor verir
    OpenCartApi.php       OpenCart veritabanına dokunan tek sınıf
    Product.php           Basit veri taşıyıcı
    ImportException.php   İnsan tarafından okunabilir içe aktarma hataları
lib/Parsedown.php     Markdown kütüphanesi (tek dosya, MIT)
products/             Senin ürün klasörlerin
tests/run.php         Veritabanı dışı kısımların testleri
docs/                 Belirtim, mimari, yol haritası, kararlar
```

## Testleri çalıştırma

```
php tests/run.php
```

Bu testler tarayıcıyı (scanner), ayrıştırıcıyı (parser) ve Markdown
dönüştürücüyü kapsar. Veritabanı tarafı gerçek bir OpenCart kurulumu
gerektirir ve bu test çalışmasına dahil değildir.
