# Mimari Kararlar

Projenin gidişatını etkileyen kararlar ve gerekçeleri. Yeni önemli bir karar
alındığında buraya kısa bir madde eklenir.

---

## Karar 1 — Ürün başına tek Markdown dosyası

Her ürün için tam olarak bir `product.md`. **Gerekçe:** içerik editörü programcı
değil; tek dosya düzenlemesi, sürümlemesi ve okunması kolaydır. **Reddedilen:**
birden çok dosya, Word, HTML düzenleme.

---

## Karar 2 — Hikâyeyi sekmelerle göstermek

Her üst düzey `#` başlığı bir sekme olur. **Gerekçe:** hikâyenin belirgin
bölümleri var (Sorun, Hayal, ...); sekmeler uzun bir sayfayı kaydırmaktan iyidir.
**Nasıl:** çerçevesiz, basit sınıflı işaretleme; sitenin kendi CSS'i ve küçük
`tabs.js`'i biçimlendirir ve çalıştırır. Ek kütüphane yok.

---

## Karar 3 — Görselleri otomatik optimize etmek

`MAX_IMAGE_WIDTH`'tan geniş görseller üretimde küçültülür. **Gerekçe:** yazar
tam boyutlu fotoğraf koyar; olduğu gibi sunmak sayfayı yavaşlatır. Kaynak
dosyalara dokunulmaz. **Reddedilen:** yazardan elle küçültmesini istemek.

---

## Karar 4 — Kart/özet için `summary` alanı

İsteğe bağlı `summary`, katalog kartlarında ve ürün sayfası başında görünür.
**Gerekçe:** kartların ve girişin kısa, okunur bir tanıtıma ihtiyacı var; ilk
paragrafı otomatik kesmek çirkin sonuç verir, açık bir alan nettir.

---

## Karar 5 — Komşu görselleri otomatik galeriye çevirmek

Arka arkaya gelen iki+ görsel bir ızgaraya sarılır; tek görsel tam genişlikte
kalır; araya metin girerse ayrı kalır. **Gerekçe:** yan yana görseller daha iyi
görünür ve yazar özel bir sözdizimi yazmamalı. **Reddedilen:** özel galeri
sözdizimi; ışık kutusu (sonraya).

---

## Karar 6 — Tüm sorunları bir arada raporlamak

Ayrıştırıcı ilk hatada durmaz; bir üründeki tüm metadata sorunlarını birlikte,
dosya yoluyla raporlar. Uyarılar (bilinmeyen alan) hatalardan ayrı tutulur.
**Gerekçe:** yazarın düzelt-çalıştır-yeni hata bul döngüsüne girmemesi.

---

## Karar 7 — `model` opsiyonel; boşsa klasör adı

`model` yazılmazsa klasör adı ürün kodu olur. **Gerekçe:** `SRV-001` gibi kodları
elle uydurmak pratik değil; klasör adı zaten benzersiz ve kararlı. **Reddedilen:**
"kategori + otomatik sıra numarası" (sıra kararlı değil, kırılgan).

---

## Karar 8 — OpenCart'ı bırakıp statik siteye geçmek

**Karar:** OpenCart entegrasyonunu tamamen kaldır; projeyi Markdown'dan statik
site üreten bağımsız bir araca dönüştür.

**Gerekçe:** İhtiyaç "hikâyeleri yayınlamak + basit, güvenli satış"tı; sipariş
paneli, müşteri hesabı, stok gerekmiyordu. OpenCart karmaşıklığının (dil
paketleri, .htaccess/SEO, önbellek, veritabanı göçleri, sürüm uyumu) çoğu bu
ihtiyaçla ilgisizdi. Statik site aynı `product.md` içeriğinden çok daha az
bakımla, veritabanı ve sunucu olmadan üretiliyor; barındırması ucuz/ücretsiz ve
düzen tamamen bizim kontrolümüzde (sekmeler, galeriler, kategori filtresi).

**Nasıl:** `SiteBuilder` + `build.php` HTML üretir; çekirdek pipeline (Scanner,
ProductParser, MarkdownRenderer, ImageOptimizer) aynen kullanılır. Satış,
barındırılan bir ödeme sağlayıcısına devredilir; indirilebilir dosyalar statik
sitede yayınlanmaz.

**Reddedilen:** OpenCart'ı sürdürmek (gereksiz karmaşıklık); iki backend'i
paralel tutmak (bakım yükü ve kafa karışıklığı).
