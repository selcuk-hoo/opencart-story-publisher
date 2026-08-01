# 3D Harikalar Diyarı

Markdown'dan statik mağaza sitesi üreten küçük bir araç.

Her ürün bir hikâyedir. Hikâyeyi tek bir Markdown dosyasına (`product.md`)
yazar, görselleri ve indirilebilir dosyaları klasöre koyarsın; `php build.php`
çalıştırınca elinde tarayıcıda açılan, her yere konabilen bir web sitesi olur.
Veritabanı yok, sunucu yazılımı yok, panel yok — sadece Markdown ve tek bir
komut.

Daha fazla belge:

- `docs/SPECIFICATION.md` — ne yaptığının tanımı
- `docs/ARCHITECTURE.md` — kodun nasıl çalıştığı (bakım için)
- `docs/MAINTENANCE.md` — kurulum, barındırma, sorun giderme
- `docs/DECISIONS.md` — mimari kararlar ve gerekçeleri
- `docs/ROADMAP.md` — yol haritası
- `talimatlar.md` — tasarım felsefesi

## Bir ürün nasıl görünür

Her ürün tek bir klasördür. Klasör adı aynı zamanda ürünün **adresi (URL) ve
kodu** olur, o yüzden açıklayıcı bir ad ver:

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

`product.md`'nin en üstünde meta veriler, altında hikâye bulunur. Her üst
düzey `#` başlığı ürün sayfasında ayrı bir **sekme** olur:

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

### Metadata alanları

| Alan | Zorunlu | Açıklama |
|---|---|---|
| `name` | Evet | Ürün adı |
| `price` | Evet | Sayı olmalı (₺) |
| `category` | Hayır | Kategori; sitede ürünler buna göre gruplanır ve filtrelenir |
| `image` | Hayır | Kapak görseli; `images/` içinde bir dosya olmalı |
| `summary` | Hayır (önerilir) | Kartlarda ve sayfa başında görünen bir-iki cümlelik özet |
| `status` | Hayır | `enabled` / `disabled` (varsayılan `enabled`) |
| `model` | Hayır | Ürün kodu; yazılmazsa klasör adı kullanılır |

### Görseller ve galeriler

- Görselleri `images/` içine koy, metinde sade adıyla göster: `![](prototip.jpg)`.
  Site üreticisi görselleri sayfanın yanına kopyalar ve yollarını düzeltir.
- Bir görselin **hangi sekmede** çıkacağını dosya adı değil, onu hangi `#`
  başlığının altına yazdığın belirler.
- Arka arkaya (aralarında yazı olmadan) yazılan iki+ görsel otomatik **yan yana
  galeri** olur.
- Büyük fotoğrafları elle küçültmene gerek yok; belirli bir genişlikten
  (varsayılan 1200 piksel) büyükse üretici otomatik küçültür, kaynak dosyaya
  dokunmaz.

## Siteyi üretme

```
php build.php
```

- Tüm ürünleri okur, `output/` klasörüne statik siteyi yazar.
- Sonucu görmek için `output/index.html`'i tarayıcıda aç, ya da hızlı bir
  sunucu çalıştır:
  ```
  php -S localhost:8000 -t output
  ```
  ve <http://localhost:8000> adresine git.
- Yayınlamak için `output/` klasörünün içeriğini herhangi bir statik
  barındırıcıya (Netlify, GitHub Pages, Cloudflare Pages) yüklersin. Ayrıntılar
  için `docs/MAINTENANCE.md`.

Gereksinim: **PHP 8+** (`gd` eklentisi görsel küçültme için önerilir).
Veritabanı gerekmez.

## Hakkımızda sayfası

Kök dizindeki isteğe bağlı `about.md` dosyasını düzenlersen, sitede otomatik
bir **Hakkımızda** sayfası oluşur ve menüye eklenir. Dosyayı silersen sayfa da
menü de kaybolur.

## Site adını ve ayarları değiştirme

`build.php`'nin başındaki birkaç satır: site adı, slogan, para birimi ve görsel
boyutu.

```php
const SITE_NAME     = '3D Harikalar Diyarı';
const SITE_TAGLINE  = 'Tasarımdan baskıya — her parçanın bir hikâyesi var.';
const CURRENCY      = '₺';
const MAX_IMAGE_WIDTH = 1200;
```

---

# Windows'ta yönetim (içerik editörü için)

Bu bölüm, siteyi güncelleyecek kişi içindir. **Programcı olmana gerek yok.**
İşin şu: Markdown yaz, görsel koy, tek komut çalıştır, sonucu tarayıcıda gör.

## 1. Tek seferlik kurulum

1. **PHP kur.** <https://windows.php.net/download> adresinden PHP 8.x zip'ini
   indir, `C:\php`'ye çıkar ve `C:\php`'yi **PATH**'e ekle. `php.ini-development`
   dosyasını `php.ini` yap, içinde `;extension=gd` satırının başındaki `;`'yi
   sil (görsel küçültme için). Kontrol: bir terminal aç, `php -v` yaz.
2. **Git for Windows** kur (<https://git-scm.com/download/win>). Sana "Git Bash"
   adında bir terminal verir.
3. **Bir metin editörü** kur — öneri **VS Code** (<https://code.visualstudio.com>)
   ya da **Obsidian**.
4. Projeyi indir (Git Bash'te; adresi sana verilecek):
   ```
   git clone <depo-adresi>
   cd 3d-harikalar-diyari
   ```

## 2. Yeni ürün ekleme

1. `products/` içinde ürün için yeni bir klasör aç. Adı **İngilizce harf, rakam,
   tire** olsun (Türkçe karakter/boşluk yok), örn. `kablo-tutucu`. Bu ad hem
   URL hem ürün kodu olur.
2. İçine `product.md` oluştur. Mevcut bir ürünü
   (örn. `products/servo-yatagi/product.md`) kopyalayıp üstünden gidebilirsin.
   Başlıktaki alanları doldur, sonra `#` başlıklarıyla hikâyeni yaz. Her başlık
   bir sekme olur.
3. Aynı klasörde `images/` ve `downloads/` oluştur; görselleri ve STL/STEP/PDF
   dosyalarını içine koy.

### Windows tuzakları
- **Gizli uzantı:** Not Defteri dosyayı gizliden `product.md.txt` yapabilir.
  Dosya Gezgini'nde **Görünüm → Dosya adı uzantıları**'nı aç, ya da VS Code
  kullan.
- **UTF-8:** Dosyayı UTF-8 kaydet (VS Code varsayılan). İçerikte Türkçe serbest;
  yalnızca dosya/klasör *adlarında* Türkçe karakter ve boşluk kullanma.
- **Satır sonları:** Dert etme, üretici ikisini de anlar.

## 3. Önizleme

Proje klasöründe:
```
php build.php
php -S localhost:8000 -t output
```
Tarayıcıda <http://localhost:8000> — değişikliğini anında görürsün.

## 4. Yayınlama

Değişikliği internete almak için iki yol var:

- **Kolay:** `output/` klasörünün içeriğini barındırıcına yükle (örn. Netlify'a
  sürükle-bırak).
- **Otomatik (önerilen, bir kez kurulur):** `git push` ile gönder; bağlı statik
  barındırıcı siteyi kendisi üretip yayınlar. Kurulumu `docs/MAINTENANCE.md`'de.

## Testleri çalıştırma (geliştirici için)

```
php tests/run.php
```

Tarayıcıyı, ayrıştırıcıyı, Markdown/sekme/galeri üreticisini ve görsel
küçültücüyü kapsar.
