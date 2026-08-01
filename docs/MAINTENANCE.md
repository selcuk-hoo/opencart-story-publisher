# Bakım ve Kurulum Notları

Projeyi çalıştıran ve yayınlayan kişi için. İçerik editörünün akışı `README.md`,
kod mimarisi `docs/ARCHITECTURE.md`.

---

## Gereksinimler

- **PHP 8 veya üzeri.**
- **`gd` eklentisi** (önerilir): görsel küçültme için. Yoksa görseller
  küçültülmeden olduğu gibi kopyalanır.
- Veritabanı, web sunucusu ya da başka bir bağımlılık **gerekmez**.

---

## Siteyi üretme

```
php build.php
```

`output/` altına tam siteyi yazar. Yerelde görmek için:

```
php -S localhost:8000 -t output
```

`output/` git'e girmez (`.gitignore`), her çalıştırmada yeniden üretilir.

---

## Yayınlama (barındırma)

Site tamamen statik olduğu için barındırma ucuz/ücretsiz ve basittir. `output/`
klasörünün **içeriği** yayınlanır.

**Seçenek A — Elle yükleme (en basit).**
`php build.php` çalıştır, `output/` içeriğini barındırıcına yükle:
- Netlify: <https://app.netlify.com/drop> adresine `output` klasörünü sürükle-bırak.
- Ya da bir FTP/statik host'a `output/` içeriğini kopyala.

**Seçenek B — Otomatik (git push → yayın).**
Kod bir statik host'a bağlanır ve her push'ta site kendiliğinden üretilip
yayınlanır. Statik host'lar PHP çalıştırmadığı için üretim adımı bir CI'da olur:

- **GitHub Pages + GitHub Actions:** bir iş akışı PHP kurar, `php build.php`
  çalıştırır ve `output/`'u Pages'e dağıtır.
- **Cloudflare Pages / Netlify:** derleme komutu olarak `php build.php`, yayın
  klasörü olarak `output` verilir (PHP destekleyen bir derleme ortamıyla).

Bu iş akışı henüz kurulu değil; istendiğinde eklenir (bkz. ROADMAP — "otomatik
yayın").

---

## Windows'ta çalıştırma

Ayrıntılı editör kılavuzu `README.md` içinde. Özet: PHP kur (PATH'e ekle, `gd`
aç), projeyi `git clone` ile al, `php build.php` çalıştır, `output/`'u tarayıcıda
aç. Veritabanı ya da sunucu gerekmez.

---

## İçerik ayarları

- **Site adı, slogan, para birimi, görsel genişliği:** `build.php` başındaki
  sabitler.
- **Hakkımızda sayfası:** kök dizindeki `about.md`. Varsa sayfa + menü oluşur;
  silinirse kaybolur.
- **Ürün ekleme:** `products/` altında yeni klasör + `product.md` (bkz. README).

---

## Sık karşılaşılan sorunlar

| Belirti | Neden ve çözüm |
|---|---|
| Görseller küçültülmüyor / hata | PHP `gd` eklentisi kapalı. `php.ini`'de `extension=gd` satırını aç. (Kapalıysa görseller yine kopyalanır, sadece küçültülmez.) |
| `Could not read product.md` | O ürün klasöründe `product.md` yok ya da adı yanlış (örn. gizli `.txt` uzantısı). |
| Bir ürün sitede yok | `php build.php` çıktısında o ürün `FAIL` mi? Metadata hatası olabilir; çıktı hangi dosya/satır olduğunu söyler. |
| Türkçe karakter bozuk | `product.md` UTF-8 kaydedilmemiş. Editörü UTF-8'e ayarla. |
| Sekmeler/filtre çalışmıyor | `output/assets/tabs.js` yüklenmiyordur; siteyi kök dizininden (index.html yanında `assets/` olacak şekilde) sun. |

---

## Test

Kod değişikliği sonrası:

```
php tests/run.php
```

Pipeline'ın (Scanner, ProductParser, MarkdownRenderer, ImageOptimizer)
tamamını kapsar; gerçek bir sunucu gerektirmez.

---

## Sürüm etiketleme

```
git tag -a vX.Y.Z -m "Kısa açıklama"
git push origin vX.Y.Z
```
