# Mimari

Bu belge, projenin bakımını yapacak kişi içindir. Kodun nasıl parçalara
ayrıldığını ve verinin nasıl aktığını anlatır.

Temel ilke: **Markdown tek doğruluk kaynağıdır.** Editör yalnızca `product.md`
yazar ve klasöre dosya koyar; gerisini üretici yapar.

---

## Genel akış (pipeline)

```
products/                 (içerik: her ürün bir klasör)
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
SiteBuilder        görselleri (ImageOptimizer ile) kopyalar, sayfaları yazar
    │
    ▼
output/                   (statik HTML sitesi — her yere konabilir)
```

Her ürün kendi başına işlenir. Bir ürünün hatası diğerlerini durdurmaz; hata
raporlanır ve sıradaki ürüne geçilir.

---

## Dosya düzeni

```
build.php             Giriş noktası — siteyi üretir
about.md              İsteğe bağlı "Hakkımızda" içeriği (varsa sayfa+menü oluşur)
src/
    Scanner.php           Ürün klasörlerini bulur
    ProductParser.php     product.md okur, doğrular, uyarı toplar
    Product.php           Basit veri taşıyıcı
    MarkdownRenderer.php  Markdown -> HTML (sekmeler, galeriler, img-fluid)
    ImageOptimizer.php    Büyük görselleri küçültür
    SiteBuilder.php       HTML sayfalarını yazar
    ImportException.php   İnsan tarafından okunabilir hata
lib/Parsedown.php     Markdown kütüphanesi (tek dosya, MIT)
assets/
    style.css             Sitenin stili (çerçevesiz)
    tabs.js               Sekme geçişi + kategori filtresi
products/             Ürün klasörleri (içerik)
output/               Üretilen site (git'e girmez)
tests/run.php         Testler
docs/                 Bu belgeler
```

Çıktının hiçbir dış isteği yoktur: `assets/` içindeki küçük CSS ve JS çıktıya
kopyalanır, görseller sayfaların yanına gömülür. Site çevrimdışı çalışır ve
herhangi bir statik barındırıcıya konabilir.

---

## Sınıflar ve sorumlulukları

### build.php (giriş noktası)
- `lib/Parsedown.php` ve `src/*` dosyalarını `require` eder (otomatik yükleyici
  yok — proje küçük).
- Ayarları (site adı, slogan, para birimi, `MAX_IMAGE_WIDTH`) sabit olarak tutar.
- Varsa `about.md`'yi okuyup `SiteBuilder`'a verir.
- `SiteBuilder`'ı kurar, `build()` çağırır, raporu yazdırır.

### Scanner
- `findProducts()`: `PRODUCTS_DIR` altındaki, içinde `product.md` olan doğrudan
  alt klasörleri bulur; sıralı mutlak yol listesi döndürür.

### ProductParser
- `parse($dir)`: `product.md`'yi okur, `Product` üretir.
- Front matter'ı elle ayrıştırır (UTF-8 BOM temizler, CRLF -> LF, `anahtar:
  değer`). YAML kütüphanesi kullanmaz.
- Tüm metadata hatalarını toplayıp tek seferde raporlar; bilinmeyen alanları
  uyarı olarak toplar. Zorunlu: `name`, `price`. `model` boşsa klasör adına düşer.

### Product
- Sade veri taşıyıcı: `slug`, `dir`, `meta[]`, `markdown`, `images[]`,
  `downloads[]`, `warnings[]`.

### MarkdownRenderer
- Parsedown'ı sarar.
- `renderTabs()`: her üst düzey `# ` başlığını bir sekmeye çevirir; başlıktan
  önceki metin (preamble) sekmelerin üstünde kalır.
- `buildGalleries()`: arka arkaya gelen 2+ görseli bir ızgaraya sarar.
- `render()`: düz makale (Hakkımızda sayfası bunu kullanır).
- Görsel `<img src>` yollarını yayınlanan konuma çevirir; `img-fluid` ekler.
- Ürettiği işaretleme çerçevesizdir; `assets/style.css` ve `tabs.js` onu
  biçimlendirir ve çalıştırır.

### ImageOptimizer
- `copy($from,$to)`: görsel `MAX_IMAGE_WIDTH`'tan genişse oran korunarak
  küçültülüp yeniden kaydedilir; değilse olduğu gibi kopyalanır. JPEG/PNG/WebP.
  GD yoksa ya da yeterince küçükse ham kopya. Kaynak dosyalara dokunmaz.

### SiteBuilder
Akışı yönetir ve HTML yazar:
1. Çıktıyı temizler, `assets/`'i kopyalar.
2. Her ürün için: parse et, görselleri `output/<slug>/images/` altına kopyala,
   `renderTabs` ile hikâyeyi üret, ürün sayfasını yaz.
3. Ana sayfayı yaz: masthead, sol kategori menüsü (filtre) ve ürün ızgarası.
4. Varsa Hakkımızda sayfasını yaz ve menüye ekle.

İndirilebilir dosyalar bilerek yayınlanmaz: satış modelinde dosyayı ödeme
sağlayıcısı ödemeden sonra teslim eder; sayfa yalnızca "bu pakette ne var"ı
listeler.

---

## Diskte ne nereye gider

| Kaynak | Hedef |
|---|---|
| `products/<slug>/images/<dosya>` | `output/<slug>/images/<dosya>` (optimize) |
| `product.md` (metadata + hikâye) | `output/<slug>/index.html` |
| `about.md` | `output/hakkimizda/index.html` |
| (tüm ürünler) | `output/index.html` (katalog) |

---

## Testler

`php tests/run.php` şunları doğrular: Scanner (klasör bulma), ProductParser
(alanlar, toplu hata, uyarılar), MarkdownRenderer (başlık/kalın, görsel URL,
img-fluid, sekmeler, galeriler), ImageOptimizer (küçültme, oran, ham kopya).

---

## Değişiklik yaparken
- Sadeliği koru (talimatlar.md).
- Mimari değişirse önce bu belgeyi güncelle.
- Önemli bir karar verdiğinde `docs/DECISIONS.md`'ye kısa bir kayıt ekle.
