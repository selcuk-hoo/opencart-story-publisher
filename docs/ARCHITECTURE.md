# Mimari

Bu belge, projenin bakımını yapacak kişi içindir. Kodun nasıl parçalara
ayrıldığını, verinin nasıl aktığını ve her sınıfın neyden sorumlu olduğunu
anlatır.

Temel ilke değişmedi: **Markdown tek doğruluk kaynağıdır.** Editör yalnızca
`product.md` yazar ve klasöre dosya koyar; gerisini içe aktarıcı yapar.

---

## Genel akış (pipeline)

```
products/
    │
    ▼
Scanner            product.md içeren klasörleri bulur
    │
    ▼
ProductParser      product.md -> Product (metadata + markdown + dosya listeleri)
    │
    ▼
MarkdownRenderer   markdown -> sekmeli HTML (galeriler + duyarlı görseller)
    │
    ▼
Publisher          görselleri (ImageOptimizer ile) ve dosyaları kopyalar,
    │              açıklamayı kurar, akışı yönetir, rapor üretir
    ▼
OpenCartApi        OpenCart veritabanına yazan tek sınıf
    │
    ▼
OpenCart veritabanı
```

Her ürün kendi başına içe aktarılır. Bir ürünün hatası diğerlerini durdurmaz;
hata raporlanır ve sıradaki ürüne geçilir.

---

## Dosya düzeni

Aynı içerikten **iki tamamen bağımsız backend** üretilir. İkisi de yalnızca
**içeriği** (`products/`) paylaşır; kodları ayrıdır. Pipeline kodu iki yerde
tekrar eder — bilinçli bir izolasyon tercihi; her tarafın neye ihtiyacı olduğu
net görünür.

```
# OpenCart tarafı (kök)
import.php            OpenCart backend'inin komut satırı girişi
config.example.php    config.php olarak kopyalanır ve doldurulur
config.php            Yerel OpenCart ayarları (git'e girmez, şifre içerir)
src/
    Scanner, ProductParser, Product, MarkdownRenderer,
    ImageOptimizer, ImportException    (pipeline)
    Publisher                          (akışı yürütür, rapor verir)
    OpenCartApi                        (veritabanına dokunan tek yer)
lib/Parsedown.php     Markdown kütüphanesi (MIT)

# Statik site tarafı (OpenCart'a hiç uzanmaz)
site/
    build.php             Statik siteyi üretir (giriş noktası)
    src/
        Scanner, ProductParser, Product, MarkdownRenderer,
        ImageOptimizer, ImportException   (pipeline kopyası)
        SiteBuilder                       (HTML dosyaları yazar)
    lib/Parsedown.php     Kendi Markdown kütüphanesi kopyası
    assets/               style.css + tabs.js (çerçevesiz)
    output/              Üretilen site (git'e girmez)

# Ortak
products/             Ürün klasörleri (bir kez yazılır)
tests/run.php         Kök src/ pipeline testleri
docs/                 Bu belgeler
```

İki backend aynı `Scanner → ProductParser → MarkdownRenderer → ImageOptimizer`
mantığını kullanır (her biri kendi kopyasıyla); yalnızca son adım farklıdır:
- **OpenCart:** `Publisher → OpenCartApi → veritabanı`.
- **Statik site:** `SiteBuilder → HTML dosyaları` (`site/output/`). Ne PHP
  sunucusu ne veritabanı gerekir; çıktı her statik barındırıcıya konabilir.
  İndirilebilir dosyalar bilerek yayınlanmaz (satış modelinde ödeme
  sağlayıcısı teslim eder).

> Bakım notu: pipeline hem `src/` hem `site/src/` içinde bulunur. Ortak
> mantıkta bir değişiklik yaparsan iki kopyaya da uygula.

---

## Sınıflar ve sorumlulukları

### import.php (giriş noktası)

- Yalnızca komut satırından çalışır.
- `config.php` yoksa açıklayıcı bir hata verip çıkar.
- `lib/Parsedown.php` ve `src/*` dosyalarını `require` eder (otomatik yükleyici
  yok — proje küçük olduğu için bilinçli).
- `MAX_IMAGE_WIDTH` ve `IMAGE_QUALITY` tanımlı değilse varsayılan verir; böylece
  eski `config.php` dosyaları değişmeden çalışmaya devam eder.
- `Publisher`'ı bağımlılıklarıyla kurar ve `importAll($onlySlug)` çağırır.
- Raporu yazdırır: her ürün için `OK`/`FAIL`, altında `note:` satırları ve sonda
  `created / updated / failed` sayımı. Hata varsa çıkış kodu 1.

Kullanım:
```
php import.php            Tüm ürünleri içe aktarır
php import.php <klasor>   Tek bir ürünü içe aktarır
```

### Scanner

- `findProducts()`: `PRODUCTS_DIR` altındaki **doğrudan** alt klasörlerden
  içinde `product.md` olanları bulur ve sıralı mutlak yol listesi döndürür.
- Ürün klasörü yoksa açıklayıcı `ImportException` fırlatır.

### ProductParser

- `parse($dir)`: `product.md`'yi okur, `Product` nesnesi üretir.
- `splitFrontMatter()`: UTF-8 BOM'u temizler, CRLF'yi LF'ye çevirir (Windows
  uyumu), baştaki `---` ile bir sonraki `---` arasındaki `anahtar: değer`
  satırlarını ayrıştırır. Değerin etrafındaki tırnaklar atılır. Front matter
  yoksa/kapanmıyorsa ya da bozuk satır varsa hata verir.
- `collectErrors()`: **tüm** metadata sorunlarını toplar (zorunlu alanlar,
  sayısal olmayan `price`, geçersiz `status`) ve tek seferde raporlar.
- `collectWarnings()`: bilinmeyen alanları uyarı olarak toplar (ölümcül değil).
- Zorunlu alanlar: `name`, `price`. `model` opsiyoneldir; boşsa klasör adına
  (slug) düşer. Bilinen alanlar: `name`, `model`, `price`, `category`,
  `image`, `status`, `summary`.

### Product

- Sade veri taşıyıcı: `slug`, `dir`, `meta[]`, `markdown`, `images[]`,
  `downloads[]`, `warnings[]`. OpenCart'ı ya da veritabanını bilmez.

### MarkdownRenderer

- Parsedown'ı sarar (`setSafeMode(false)` — içerik yazarın kendisinindir).
- `render($md, $imageUrls)`: Parsedown -> görsel URL'lerini yaz -> galerileri
  kur -> `img-fluid` ekle.
- `renderTabs($md, $imageUrls, $idPrefix)`: her üst düzey `# ` başlığını bir
  Bootstrap 5 sekmesine çevirir. Başlıktan önceki metin (preamble) sekmelerin
  üstünde kalır. Hiç `# ` başlığı yoksa düz `render()`'a döner.
- `buildGalleries()`: arka arkaya gelen 2+ görseli Bootstrap `row`/`col`
  ızgarasına sarar. İki görsel `col-md-6`, üç+ görsel `col-md-4`, telefonda hep
  `col-6`. Tek görsel dokunulmaz; araya metin girerse birleşmez.
- `makeImagesResponsive()`: sınıfı olmayan `<img>`'lere `img-fluid` ekler.

Bu HTML doğrudan OpenCart **ürün açıklaması** alanına yazılır. Sekmeler ve
galeriler kendi kendine yeten Bootstrap 5 işaretlemesidir; OpenCart 4 zaten
Bootstrap 5 yüklediği için **hiçbir tema/şablon düzenlenmez**.

### ImageOptimizer

- `copy($from, $to)`: görsel `MAX_IMAGE_WIDTH`'tan genişse en-boy oranını
  koruyarak küçültür ve yeniden kaydeder; değilse dosyayı olduğu gibi kopyalar.
- JPEG, PNG, WebP'yi yeniden boyutlandırır (PNG/WebP saydamlığı korunur).
  Tanınmayan tür, GD yokluğu ya da yeterince küçük görsel -> ham kopya.
- Kaynak (`products/...`) dosyalarına asla dokunmaz; yalnızca OpenCart'a giden
  kopya optimize edilir.

### Publisher

Akışı yönetir (docs/SPECIFICATION.md, "İçe aktarma süreci" adımları):

1. `Scanner` ile klasörleri bulur.
2. `ProductParser` ile okur/doğrular (uyarıları alır).
3. `MarkdownRenderer::renderTabs` ile HTML üretir; en üste `summary` paragrafını
   ekler (liste önizlemesi ve kısa giriş için).
4. `copyImages`: görselleri `ImageOptimizer` ile
   `OPENCART_IMAGE_DIR/catalog/story/<slug>/` altına kopyalar; `image:` alanının
   `images/` içinde bulunduğunu doğrular.
5. `copyDownloads`: indirilebilir dosyaları `OPENCART_DOWNLOAD_DIR` altına
   `<slug>-<dosya>` sabit adıyla kopyalar (tekrar içe aktarma orijinali ezer,
   yetim dosya kalmaz).
6. Bir işlem (transaction) içinde `OpenCartApi`'yi çağırır: ürünü modele göre
   bul/oluştur/güncelle, açıklamayı yaz, mağazaya bağla, SEO URL, downloads,
   kategori (yoksa oluştur) ve bağla. Hata olursa `rollback`.
7. Sonucu (`created`/`updated`) ve uyarıları rapora döndürür.

Görsel URL'leri: `imageUrlMap()`, her görsel adını
`OPENCART_IMAGE_URL + 'catalog/story/<slug>/<dosya>'` genel URL'sine eşler;
`renderTabs` bu eşlemeyle `<img src>`'leri düzeltir. Ana ürün görseli
(`mainImagePath()`) ise OpenCart'ın sakladığı biçimde (`image/` klasörüne göreli)
`catalog/story/<slug>/<dosya>` olarak kaydedilir.

### OpenCartApi

OpenCart hakkında bilgi sahibi **tek** sınıftır; geri kalan kod düz PHP kalır.
`mysqli` ile bağlanır (`DB_*` ayarları, `utf8mb4`). Tüm tablolar `DB_PREFIX`
öneki ile kullanılır (örn. `oc_product`).

Başlıca metotlar:
- `begin/commit/rollback` — ürün başına işlem.
- `findProductIdByModel`, `createProduct`, `updateProduct`, `saveDescription`,
  `linkStore`, `saveSeoUrl`.
- `findCategoryIdByName`, `createCategory`, `linkProductToCategory`.
- `replaceDownloads`.
- `slug()` — kategori SEO anahtarı üretir; Türkçe karakterleri çevirir
  (`Kalıplar -> kaliplar`).

Yazılan OpenCart tabloları:
- Ürün: `product`, `product_description`, `product_to_store`,
  `product_to_category`, `seo_url` (`key = 'product_id'`).
- Kategori (otomatik oluşturulursa): `category`, `category_description`,
  `category_to_store`, `category_path`, `seo_url` (`key = 'category_id'`).
- İndirmeler: `download`, `download_description`, `product_to_download`.

`createProduct`, OpenCart 4 şemasında `NOT NULL` olup varsayılanı olmayan
sütunları (sku, upc, ean, ...) açıkça boş string yapar; böylece MySQL katı
(strict) modda da çalışır. Ürünler dijital kabul edilir: `subtract = 0`,
yüksek `quantity`.

---

## Diskte ve veritabanında ne nereye gider

| Kaynak | Hedef |
|---|---|
| `products/<slug>/images/<dosya>` | `OPENCART_IMAGE_DIR/catalog/story/<slug>/<dosya>` (optimize) |
| `products/<slug>/downloads/<dosya>` | `OPENCART_DOWNLOAD_DIR/<slug>-<dosya>` |
| `product.md` metadata + hikâye | `oc_product` + `oc_product_description` (ve ilgili bağlantı tabloları) |

---

## Yapılandırma (config.php)

| Sabit | Açıklama |
|---|---|
| `DB_HOSTNAME/USERNAME/PASSWORD/DATABASE/PORT/PREFIX` | OpenCart veritabanı (OpenCart config.php'sinden kopyalanır) |
| `PRODUCTS_DIR` | Ürün klasörlerinin yeri |
| `OPENCART_IMAGE_DIR` | OpenCart `image/` klasörü |
| `OPENCART_IMAGE_URL` | Görsellerin genel URL öneki (alt klasör kurulumunda kök-göreli olmalı, örn. `/opencart/image/`) |
| `OPENCART_DOWNLOAD_DIR` | OpenCart indirme depolama klasörü |
| `OPENCART_LANGUAGE_ID` / `OPENCART_STORE_ID` | Ürünlerin oluşturulduğu dil ve mağaza |
| `MAX_IMAGE_WIDTH` / `IMAGE_QUALITY` | Görsel optimizasyonu (opsiyonel; varsayılan 600 / 82) |

---

## Testler

`php tests/run.php` şu veritabanı gerektirmeyen kısımları doğrular:
- **Scanner**: doğru klasörleri bulma, sıralama, eksik klasör hatası.
- **ProductParser**: alan okuma, tüm hataları birden raporlama, uyarılar,
  tırnak temizleme, front matter kontrolleri.
- **MarkdownRenderer**: başlık/kalın, görsel URL yeniden yazma, `img-fluid`,
  sekmeler, galeriler.
- **ImageOptimizer**: büyük görseli küçültme, oranı koruma, küçük görsele
  dokunmama, görsel olmayan dosyayı kopyalama.

`OpenCartApi` ve `Publisher`'ın veritabanı tarafı gerçek bir OpenCart kurulumu
gerektirdiği için bu testlere dahil değildir; değişiklik sonrası gerçek bir
kurulumda denenmelidir.

---

## Değişiklik yaparken

- Önce mevcut mimariyi anla, sadeliği koru (talimatlar.md).
- OpenCart'a özgü her şey `OpenCartApi` içinde kalsın; diğer sınıflar düz PHP
  olarak dursun.
- Mimari değişirse **önce bu belgeyi** güncelle. Güncel olmayan doküman bir
  hatadır.
- Önemli bir karar verdiğinde `docs/DECISIONS.md`'ye kısa bir kayıt ekle.
