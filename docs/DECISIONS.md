# Mimari Kararlar

Bu belge, projenin gidişatını etkileyen kararları ve gerekçelerini kaydeder.
Yeni önemli bir karar alındığında buraya kısa bir madde eklenir.

---

## Karar 001 — Ürün başına tek Markdown dosyası

**Karar:** Her ürün için tam olarak bir Markdown dosyası kullan.

**Gerekçe:** İçerik editörü programcı değildir.

**Avantajlar:** Daha kolay düzenleme, sürüm kontrolü, içe aktarma ve
belgeleme.

**Reddedilen alternatifler:** Birden çok Markdown dosyası, Word belgeleri,
HTML düzenleme.

---

## Karar 002 — Hikâyeyi sekmelerle göstermek

**Karar:** Hikâyeyi, her üst düzey `# ` başlığı bir sekme olacak şekilde
göster.

**Gerekçe:** Hikâyenin belirgin bölümleri var (Sorun, Hayal, ...). Sekmeler,
müşterinin uzun bir sayfayı kaydırmak yerine bölümler arasında gezinmesini
sağlar.

**Nasıl:** Sekme işaretlemesi, ürün açıklamasının içine yazılan kendi kendine
yeten Bootstrap 5'tir. OpenCart 4 zaten Bootstrap 5 yükler, dolayısıyla hiçbir
tema/şablon değişmez. Markdown, editörün dokunduğu tek şey olarak kalır.

**Reddedilen alternatifler:** OpenCart ürün sayfası şablonunu düzenleyip yerel
sekmeler eklemek ("şablonları asla düzenleme" kuralını çiğner); kendi
controller/view'ı olan ayrı bir eklenti modülü (bir blok HTML'in yapabileceği
iş için fazla).

---

## Karar 003 — İçe aktarmada görselleri otomatik optimize etmek

**Karar:** Görselleri içe aktarma sırasında otomatik optimize et.

**Gerekçe:** Yazar, tam boyutlu telefon/kamera fotoğraflarını `images/` içine
koyar. Bunları olduğu gibi sunmak ürün sayfasını yavaşlatır.

**Nasıl:** OpenCart'a kopyalarken `MAX_IMAGE_WIDTH`'tan geniş görseller
küçültülüp yeniden kaydedilir (`ImageOptimizer`). `products/` içindeki kaynak
dosyalar değişmez. Hikâye görselleri ayrıca Bootstrap `img-fluid` sınıfı alır,
böylece sayfadan taşmaz.

**Reddedilen alternatifler:** Yazardan görselleri elle küçültmesini istemek
(yazar yalnızca yazıp dosya koymalı); ayrı bir yapı adımı veya harici araç
(GD zaten mevcut ve işi görüyor).

---

## Karar 004 — Liste metni için `summary` alanı

**Karar:** Liste metni için opsiyonel bir `summary` alanı ekle.

**Gerekçe:** OpenCart, kategori/arama listelerinde açıklamanın etiketleri
soyulmuş bir önizlemesini gösterir. Sekmeli bir açıklamada bu önizleme, sekme
etiketlerinin yan yana yapışmasına dönüşür ("SorunHayalIlkPrototip...") ve
anlamsızdır.

**Nasıl:** Yazar front matter'a bir-iki cümlelik `summary` yazar. Bu, açıklamanın
en üstüne düz bir paragraf olarak konur; ürün sayfasında kısa bir giriş,
listede ise önizleme metni olur.

**Reddedilen alternatifler:** Liste şablonunu `meta_description` kullanacak
şekilde düzenlemek ("şablonları asla düzenleme" kuralını çiğner); özeti ilk
paragraftan otomatik üretmek (o paragrafı sayfada tekrar ederdi; açık bir alan
daha nettir).

---

## Karar 005 — Kategori yoksa otomatik oluşturmak

**Karar:** Kategori mevcut değilse otomatik oluştur.

**Gerekçe:** v0.1 yalnızca var olan kategoriye bağlanıyordu; bu yüzden yazarın
içe aktarmadan önce OpenCart'a girip elle kategori eklemesi gerekiyordu. Bu,
"yalnızca Markdown'a dokun" akışını bozar.

**Nasıl:** Bir ürün, bulunmayan bir kategori adı verdiğinde içe aktarıcı onu
üst düzey bir kategori olarak oluşturur (`findCategoryIdByName` /
`createCategory`) ve kısa bir not yazdırır. Yanlış yazılmış bir kategori yeni
bir kategori oluşturacağından, not bunu görünür kılar.

**Reddedilen alternatifler:** "Kategori önceden var olmalı" kuralını korumak
(yazarı Markdown'dan çıkarıp yönetim paneline sokar); yakın kategori adlarını
tahmin/bulanık eşleştirme (sürpriz; tam adlar öngörülebilir).

---

## Karar 006 — Arka arkaya görselleri otomatik galeriye çevirmek

**Karar:** Komşu görselleri otomatik olarak galeriye çevir.

**Gerekçe:** Arka arkaya birkaç görsel, alt alta tam genişlik yerine yan yana
daha iyi görünüyordu ve yazarın özel bir işaretleme yazması gerekmemeli.

**Nasıl:** İki veya daha fazla görsel yan yana geldiğinde (arka arkaya satırlar
ya da art arda tek görselli paragraflar), `MarkdownRenderer` bunları bir
Bootstrap ızgarasına (`story-gallery`) sarar. Tek görsel tam genişlikte kalır.
Aralarına metin giren görseller ayrı kalır.

**Reddedilen alternatifler:** Özel bir galeri sözdizimi veya kısa kod (yazar
yalnızca düz Markdown yazmalı); ışık kutusu / tıkla-büyüt (ek JavaScript;
gerekirse sonra eklenebilir).

---

## Karar 007 — Tüm sorunları bir arada raporlamak, uyarıyı hatadan ayırmak

**Karar:** Bir üründeki tüm sorunları bir arada raporla; uyarıları hatalardan
ayır.

**Gerekçe:** Ayrıştırıcı ilk bozuk alanda duruyordu; yazar bir şeyi düzeltip
tekrar çalıştırıyor ve bir sonrakini buluyordu. Uyarılar da doğrudan ekrana,
sonuçların arasına karışıyordu.

**Nasıl:** `ProductParser` her metadata sorununu toplayıp birlikte raporlar ve
`product.md` dosyasını işaret eder. Ölümcül olmayan durumlar (bilinmeyen alan,
oluşturulan kategori) rapora uyarı olarak taşınır ve her ürünün altında girintili
`note:` satırları olarak yazdırılır; böylece hatalar ve notlar birbirine
karışmaz.

**Reddedilen alternatifler:** İlk hatada durmak (yazar için yavaş gidiş-geliş);
bir loglama çerçevesi (küçük bir içe aktarıcı için fazla).

---

## Karar 008 — `model` opsiyonel; boşsa klasör adı

**Karar:** `model` alanını opsiyonel yap; yazılmazsa klasör adını (slug)
benzersiz kod olarak kullan.

**Gerekçe:** `SRV-001` gibi kodları elle uydurmak pratik değildi. Klasör adı
zaten benzersiz ve kararlıdır, dolayısıyla iyi bir varsayılandır.

**Nasıl:** `ProductParser`, `model` boşsa `meta['model']`'i klasör adına
eşitler. Açıklayıcı bir klasör adı (`servo-yatagi`) hem URL slug'ı hem de ürün
kodu olur. Belirli bir kod biçimi gerekiyorsa `model:` yine elle yazılabilir.

**Reddedilen alternatifler:** Kodu "kategori + otomatik sıra numarası" olarak
üretmek (sıra numarası kararlı olmadığından tekrar içe aktarmada duplicate
riski taşır ve idempotency'yi bozar).

---

## Karar 009 — SEO anahtarlarını benzersiz tut

**Karar:** Bir SEO anahtarı (keyword) yazılırken, o anahtarı tutan başka her
kayıt da silinsin; anahtar her zaman tek bir şeye işaret etsin.

**Gerekçe:** Eski `saveSeoUrl` yalnızca aynı ürünün eski kaydını siliyordu.
Silinen ürünlerden kalan artık SEO kayıtları ya da aynı anahtarı yazan yeni
kayıtlar `oc_seo_url`'de çakışma yaratıp storefront'ta "SEO URL açık" iken
kırık linklere/404'e yol açıyordu.

**Nasıl:** `writeSeoUrl`, hem bu kaydın eski anahtarını hem de aynı anahtarı
(store+dil bazında) tutan diğer kayıtları silip yeniden yazar. Böylece tekrar
içe aktarma kendi kendini onarır ve artık kayıtlar temizlenir. Hem ürün hem
kategori SEO'su bunu kullanır.

**Reddedilen alternatifler:** Anahtar çakışmasını görmezden gelmek (kırık
linkler); OpenCart'ın SEO şablonunu/çekirdeğini değiştirmek (kuralımıza
aykırı).

---

## Karar 010 — Statik siteyi OpenCart'tan tamamen izole etmek

**Karar:** Statik site backend'ini `site/` altında, kendi pipeline ve Markdown
kütüphanesi kopyasıyla tamamen bağımsız yap; OpenCart tarafının koduna hiç
uzanma.

**Gerekçe:** İki backend'in neye ihtiyacı olduğunu net görmek ve statik
tarafın OpenCart tarafındaki değişikliklerden etkilenmemesini sağlamak
istendi. Ortak `src/` üzerinden bağ, "hangi kod hangi tarafa ait" ayrımını
bulanıklaştırıyordu.

**Nasıl:** `site/` kendi `src/` (Scanner, ProductParser, Product,
MarkdownRenderer, ImageOptimizer, ImportException, SiteBuilder) ve
`lib/Parsedown.php` kopyasını taşır. Yalnızca içerik (`products/`) paylaşılır;
çünkü product.md'yi iki kez yazmak istenmez.

**Bedeli (kabul edildi):** Pipeline kodu iki yerde tekrar eder; ortak bir
değişiklik iki kopyaya da uygulanmalıdır.

**Reddedilen alternatifler:** Ortak `src/` üzerinden paylaşım (izolasyonu ve
netliği bozuyordu); içeriği de kopyalamak (product.md'lerin iki kez
yazılmasına yol açardı).
