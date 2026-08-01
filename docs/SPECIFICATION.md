# 3D Harikalar Diyarı — Spesifikasyon

Bu belge projenin güncel durumunu tanımlar. Mimari için `docs/ARCHITECTURE.md`,
kararlar için `docs/DECISIONS.md`, yol haritası için `docs/ROADMAP.md`.

---

## 1. Amaç

Mühendislik ve maker projelerini yayınlayan, satan bir **statik mağaza sitesi**
üreticisi. Geleneksel bir e-ticaret sisteminden farklı olarak her ürün bir
tasarım yolculuğunu anlatır. Hikâye asıl içeriktir; satış ikincildir.

Site statiktir: veritabanı ya da sunucu yazılımı gerektirmez, herhangi bir
statik barındırıcıya konabilir.

---

## 2. Tasarım felsefesi

Proje bilinçli olarak basittir. İçerik üreticisi yalnızca Markdown yazar ve bir
klasöre dosya koyar; gerisi otomatik olur:

Markdown yaz → Görselleri koy → Dosyaları koy → `php build.php` → Site hazır.

---

## 3. Ürün yapısı

Her ürün tek bir klasördür:

```
products/servo-yatagi/
    product.md
    images/    (kapak.jpg, prototip.jpg, ...)
    downloads/ (servo-yatagi.stl, servo-yatagi.step)
```

Klasör adı ürünün URL'i ve kodudur.

---

## 4. product.md

İki bölümden oluşur: üstte metadata (front matter), altta Markdown hikâye.

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

### Metadata

| Alan | Zorunlu | Açıklama |
|---|---|---|
| `name` | Evet | Ürün adı |
| `price` | Evet | Sayı |
| `category` | Hayır | Katalogda gruplama ve sol menü filtresi buna göre |
| `image` | Hayır | Kapak görseli (`images/` içinde) |
| `summary` | Hayır | Kart ve sayfa başı özeti |
| `status` | Hayır | `enabled`/`disabled` (varsayılan `enabled`) |
| `model` | Hayır | Ürün kodu; boşsa klasör adı |

Bilinmeyen alan hata değildir; uyarı olarak raporlanır.

---

## 5. Hikâye sekmeleri

Her üst düzey `#` başlığı ürün sayfasında ayrı bir sekme olur. Önerilen sıra:
Sorun, Hayal, İlk Prototip, Geliştirme, Paylaşım, İlham — ama sabit değildir.
Yazar istediği başlığı, istediği dilde kullanır.

---

## 6. Görseller ve galeriler

Görseller `images/` içinde saklanır, Markdown'da sade adıyla gösterilir. Bir
görselin hangi sekmede çıkacağını, onu hangi başlığın altına yazdığın belirler.
Arka arkaya gelen iki+ görsel otomatik yan yana galeri olur.

---

## 7. Görsel optimizasyonu

`MAX_IMAGE_WIDTH`'tan (varsayılan 1200 px) geniş görseller, üretim sırasında
oran korunarak küçültülür. Kaynak dosyalara dokunulmaz.

---

## 8. Katalog ve gezinme

Ana sayfa: büyük başlık (masthead), solda kategori menüsü ve ürün ızgarası. Sol
menüde bir kategoriye tıklamak ızgarayı o kategoriye filtreler ("Tüm Ürünler"
hepsini gösterir). Çok sayıda üründe kategoriler bu sayede erişilebilir kalır.

---

## 9. Hakkımızda sayfası

Kök dizindeki isteğe bağlı `about.md` varsa, ondan bir Hakkımızda sayfası
üretilir ve menüye/altbilgiye eklenir. Yoksa sayfa da bağlantı da oluşmaz.

---

## 10. Satış ve indirmeler

İndirilebilir dosyalar (STL/STEP/...) **statik sitede yayınlanmaz**. Ürün sayfası
"bu pakette ne var"ı listeler ve bir "Satın Al" bağlantısı sunar. Ödeme ve
dosya teslimi barındırılan bir ödeme sağlayıcısına devredilir (planlanan:
iyzico/PayTR + güvenli teslim). Böylece hesap/sipariş paneli gerekmez.

---

## 11. Çıktı

`php build.php`, `output/` altına tam bir statik site yazar: katalog, ürün
sayfaları, Hakkımızda, kopyalanmış görseller ve `assets/`. Çıktı çevrimdışı
çalışır ve herhangi bir statik barındırıcıya konabilir.

---

## 12. Hata raporlama

Hatalar insan tarafından okunabilir olmalıdır. Bir üründeki tüm metadata
sorunları tek seferde, dosya yoluyla raporlanır. Bir ürünün hatası diğerlerini
durdurmaz. Bilgilendirici durumlar uyarı olarak gösterilir.

---

## 13. Gelecek

Olası geliştirmeler: gerçek ödeme entegrasyonu, arama, çok dillilik, otomatik
yayın (CI). Durum için `docs/ROADMAP.md`.
