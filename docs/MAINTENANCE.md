# Bakım ve Kurulum Notları

Bu belge, projeyi bir sunucuda çalıştırıp bakımını yapan kişi içindir.
İçerik editörünün akışı için `README.md`'ye, kod mimarisi için
`docs/ARCHITECTURE.md`'ye bakınız.

---

## Gereksinimler

- **OpenCart 4** kurulumu.
- **PHP 8 veya üzeri**, şu eklentilerle:
  - `mysqli` — veritabanı erişimi (zorunlu).
  - `gd` — görsel optimizasyonu (önerilir; yoksa görseller küçültülmeden
    olduğu gibi kopyalanır).
- Veritabanı erişimi olan bir kabuk (import genellikle OpenCart'ın bulunduğu
  sunucuda çalışır).

---

## İlk kurulum

1. Depoyu OpenCart'ın **dışında** bir yere klonla (aynı sunucuda olması
   yeterli; OpenCart klasörünün içinde olması gerekmez).
2. Yapılandırmayı oluştur:
   ```
   cp config.example.php config.php
   ```
3. `config.php`'yi doldur. `DB_*` değerlerini OpenCart'ın kendi
   `config.php`'sinden kopyala. Klasör yolları için OpenCart config.php'sindeki
   `DIR_IMAGE` ve `DIR_STORAGE` sabitlerine bak:
   - `OPENCART_IMAGE_DIR` = `DIR_IMAGE` (örn. `/var/www/html/opencart/image`)
   - `OPENCART_DOWNLOAD_DIR` = `DIR_STORAGE` + `download`
     (örn. `/var/www/html/opencart/system/storage/download`)
4. İçe aktar:
   ```
   php import.php
   ```

`config.php` şifre içerdiği için `.gitignore`'dadır; sunucuda kalır, depoya
girmez.

---

## Dosya izinleri

İçe aktarıcıyı çalıştıran kullanıcı ile OpenCart'ı çalıştıran web sunucusu
(genellikle `www-data`) **aynı** klasörlere yazabilmelidir:

- `image/` — içe aktarıcı hikâye görsellerini `image/catalog/story/...` altına
  yazar; web sunucusu da `image/cache/...` altına küçük resimler üretir.
- `system/storage/` — indirmeler ve önbellek.

Yerel geliştirmede en pratik çözüm (yalnızca yerel için):
```
sudo chmod -R 777 /var/www/html/opencart/image /var/www/html/opencart/system/storage
```
Canlı sunucuda bunun yerine klasörleri `www-data` grubuna verip grup-yazma
iznini kullan.

---

## Alt klasör kurulumu ve görsel URL'leri

OpenCart bir alt klasörde çalışıyorsa (örn. `http://site/opencart/`),
`config.php`'de `OPENCART_IMAGE_URL` değerini **kök-göreli** yap:

```php
define('OPENCART_IMAGE_URL', '/opencart/image/');
```

Aksi halde SEO URL'li ürün sayfalarında hikâye içindeki görsellerin yolu
yanlış çözülüp 404 verebilir. Bu ayar yalnızca hikâye içindeki `<img>`
yollarını etkiler; ana ürün görselini OpenCart kendi ayarına göre boyutlandırır.

---

## Önbellek

İçe aktarma OpenCart veritabanına doğrudan yazdığı için, değişiklikleri
görmek için önbelleği temizle:

```
rm -rf /var/www/html/opencart/system/storage/cache/*
rm -rf /var/www/html/opencart/image/cache/*
```

İlki ürün/kategori önbelleği, ikincisi yeniden boyutlanmış görsel önbelleğidir.

---

## Dil ve mağaza

`config.php`'deki `OPENCART_LANGUAGE_ID`, mağazanın aktif diliyle **aynı**
olmalıdır. Yanlış dil ID'si, ürün açıklamasının yanlış dile yazılmasına ve
storefront'ta ürünün "yok" gibi davranmasına yol açar. Kontrol:

```
SELECT language_id, name, code, status FROM oc_language;
```

`OPENCART_STORE_ID` genellikle `0`'dır (varsayılan mağaza).

---

## PHP 8.5 uyarısı

PHP 8.5 çalıştırıyorsan, ürün sayfasında OpenCart'ın **kendi çekirdeğinden**
(`system/library/image.php`) `imagedestroy() is deprecated` gibi uyarılar
görebilirsin. Bu bizim eklentimizden gelmez; OpenCart 4'ün henüz PHP 8.5'e
tam uyumlu olmamasındandır ve zararsızdır.

Ekranda görünmesini kapatmak için (çekirdeği düzenlemeden):
**Admin → System → Settings → (mağaza) Edit → Server sekmesi →
Display Errors = Disabled.**

OpenCart çekirdeğindeki dosyaları düzenleme; güncellemede üzerine yazılır.

---

## Sık karşılaşılan sorunlar

| Belirti | Olası neden ve çözüm |
|---|---|
| Görseller yüklenmiyor | İzin sorunu (`image/cache` yazılamıyor) ya da alt klasörde `OPENCART_IMAGE_URL` kök-göreli değil. Önbelleği de temizle. |
| Ürün kategoride görünmüyor | Kategori adı `product.md` ile birebir eşleşmiyor ya da `language_id` farklı. Artık yoksa otomatik oluşturulur; yanlış yazım yanlış kategori yaratır. |
| Listede anlamsız metin (`SorunHayal...`) | `product.md`'de `summary` alanı eksik. Bir-iki cümlelik özet ekle. |
| `imagedestroy() is deprecated` uyarıları | PHP 8.5 + OpenCart çekirdeği. Display Errors'ı kapat (yukarı bkz.). |
| Ürün mağazada hiç görünmüyor | `status: disabled`, yanlış `language_id`, `product_to_store` eksik ya da önbellek. |
| `Database error` | OpenCart 4 şemasında bir sütun farkı olabilir. `OpenCartApi.php`'deki ilgili sorguya bakın; hatayı olduğu gibi kaydedin. |

---

## Test

Kod değişikliği sonrası, veritabanı gerektirmeyen kısımları çalıştır:

```
php tests/run.php
```

Veritabanı tarafı (`OpenCartApi`, `Publisher`'ın DB adımları) gerçek bir
OpenCart kurulumunda, tercihen bir yedek/staging kopyasında denenmelidir.
İlk denemede küçük bir ürün (`php import.php <slug>`) ile başla, admin ve
storefront'ta doğrula, sonra tümünü içe aktar.

---

## Yeni sürüm etiketleme (tag)

Anlamlı bir özellik grubu tamamlanınca sürüm etiketi at:

```
git tag -a vX.Y.Z -m "Kısa açıklama"
git push origin vX.Y.Z
```

Etiket belirli bir commit'i kalıcı olarak işaretler ve dallardan bağımsızdır.
