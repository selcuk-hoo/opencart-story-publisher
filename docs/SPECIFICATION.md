# Story Publisher Spesifikasyonu

Sürüm: 0.2

> Not: Bu belge projenin **güncel** durumunu anlatır. Tarihsel v0.1 kapsamı
> için git geçmişine ve `docs/ROADMAP.md`'ye bakılabilir. Mimari ayrıntılar
> `docs/ARCHITECTURE.md`, kararların gerekçeleri `docs/DECISIONS.md`
> içindedir.

---

## 1. Amaç

Story Publisher, mühendislik ve maker projelerini yayınlamak için bir
OpenCart 4 aracıdır.

Geleneksel bir e-ticaret sisteminden farklı olarak her ürün bir tasarım
yolculuğunu temsil eder. Amaç yalnızca dijital dosya satmak değil, tasarımın
nasıl ve neden evrildiğini de anlatmaktır.

Hikâye asıl içeriktir. Mağaza yalnızca yayınlama platformudur.

---

## 2. Tasarım felsefesi

Proje bilinçli olarak basittir. İçerik üreticisi yalnızca Markdown yazar ve
bir klasöre dosya koyar; gerisi otomatik olur.

Hedeflenen tam akış:

Markdown yaz → Görselleri koy → İndirilebilir dosyaları koy → İçe aktar →
Ürün OpenCart'ta belirir.

---

## 3. Ürün yapısı

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

Başka dosya gerekmez.

---

## 4. product.md

Her ürün tam olarak bir Markdown belgesi içerir. İki bölümden oluşur:

1. Metadata (front matter)
2. Markdown gövdesi (hikâye)

Örnek:

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

---

## 5. Metadata alanları

| Alan | Zorunlu | Açıklama |
|---|---|---|
| `name` | Evet | Ürün adı |
| `model` | Evet | Benzersiz tanımlayıcı; tekrar içe aktarma bu alana göre günceller |
| `price` | Evet | Sayı olmalıdır |
| `category` | Hayır | Ürün kategorisi; yoksa otomatik oluşturulur (bkz. 11) |
| `image` | Hayır | Kapak görseli; `images/` içinde bir dosya olmalı |
| `summary` | Hayır (önerilir) | Liste önizlemesi ve kısa giriş metni (bkz. 9) |
| `status` | Hayır | `enabled` veya `disabled` (varsayılan `enabled`) |

Bilinmeyen bir alan hata değildir; uyarı olarak raporlanır ve yok sayılır.

---

## 6. Hikâye bölümleri ve sekmeler

Hikâye, üst düzey `#` başlıklarıyla bölümlere ayrılır. **Her `#` başlığı, ürün
sayfasında ayrı bir sekme olur.** Başlık metni sekmenin etiketidir.

Önerilen bölüm sırası: Sorun, Hayal, İlk Prototip, Geliştirme, Paylaşım, İlham.
Ancak sabit bir başlık kümesi zorunlu değildir; yazar istediği başlıkları,
istediği dilde kullanabilir.

Sekmeler, ürün açıklamasının içine yazılan kendi kendine yeten Bootstrap 5
işaretlemesidir; OpenCart teması/şablonu düzenlenmez.

---

## 7. Görseller

Görseller `images/` içinde saklanır ve Markdown'da sade adlarıyla gösterilir:

```
![](prototip.jpg)
```

İçe aktarıcı görselleri OpenCart'a kopyalar ve yolları otomatik düzeltir.
Yazar asla OpenCart yolu yazmaz.

---

## 8. Otomatik galeriler

Arka arkaya (aralarında metin olmadan) yazılan iki veya daha fazla görsel,
otomatik olarak yan yana bir ızgara (galeri) olarak gösterilir. Tek görsel
tam genişlikte kalır. Ek bir sözdizimi gerekmez.

---

## 9. Görsel optimizasyonu

Belirli bir genişlikten (`MAX_IMAGE_WIDTH`, varsayılan 600 piksel) büyük
görseller, içe aktarma sırasında en-boy oranı korunarak küçültülür ve yeniden
kaydedilir. Kaynak dosyalara dokunulmaz. Böylece büyük fotoğraflar sayfayı
yavaşlatmaz.

---

## 10. Özet (summary)

`summary` alanı bir-iki cümlelik düz metin özettir. Ürün açıklamasının en
üstüne kısa bir giriş olarak yerleştirilir ve OpenCart'ın kategori/arama
listelerinde gösterdiği önizleme metnidir.

Bir sekmeli açıklamada OpenCart, önizleme için HTML'i soyduğunda sekme
etiketleri yan yana yapışır ve anlamsız görünür. `summary`, bu önizlemeyi
temiz ve okunur tutar.

---

## 11. Kategoriler

`category` alanındaki kategori OpenCart'ta varsa ürün ona bağlanır. Yoksa içe
aktarıcı onu üst düzey bir kategori olarak **otomatik oluşturur** ve bir not
raporlar. Böylece yeni kategori için OpenCart'a girmeye gerek kalmaz.

Kategori adının yazımına dikkat edilmelidir; yanlış yazım, yanlış adda yeni bir
kategori oluşturur.

---

## 12. İndirilebilir dosyalar

İndirilebilir dosyalar `downloads/` içinde saklanır. Desteklenen örnek
biçimler: STL, STEP, ZIP, PDF. İçe aktarıcı bunları OpenCart depolamasına
kopyalar ve ürüne bağlar. Tekrar içe aktarma, aynı ürünün eski indirmelerini
temizleyip yeniden ekler.

---

## 13. İçe aktarma süreci

İçe aktarıcı her ürün için şu adımları izler:

1. Ürün klasörlerini bul.
2. product.md'yi oku.
3. Metadatayı doğrula.
4. Markdown'ı sekmeli HTML'e çevir.
5. Görselleri kopyala (gerekirse küçülterek).
6. İndirilebilir dosyaları kopyala.
7. OpenCart ürününü oluştur veya güncelle (kategori yoksa oluştur).
8. Başarı veya başarısızlığı raporla.

---

## 14. Güncelleme ve benzersizlik

Aynı ürünü tekrar içe aktarmak mevcut ürünü günceller; kopya oluşturmaz.
Benzersiz tanımlayıcı olarak ürünün `model` alanı kullanılır.

---

## 15. Ürün kaldırma

İçe aktarıcı ürün **silmez**. Bir ürünü kaldırmak için iki yol vardır:

- Mağazadan gizlemek: product.md'de `status: disabled` yapıp tekrar içe
  aktarmak (geri döndürülebilir, veriler kalır).
- Kalıcı silmek: OpenCart admin panelinden ürünü silmek.

`products/` altından klasörü silmek, ürünü OpenCart'tan kaldırmaz.

---

## 16. Hata raporlama

Hatalar her zaman insan tarafından okunabilir olmalıdır. Bir üründeki tüm
metadata sorunları tek seferde, hangi dosyada olduğu belirtilerek raporlanır.
Bir ürünün hatası diğerlerini durdurmaz. Bilgilendirici notlar (örneğin
kategori oluşturma) hata değil, uyarı olarak gösterilir.

Örnek:

```
Missing field: price
```

yerine:

```
Database error.
```

asla gösterilmez.

---

## 17. Performans

Doğruluk hızdan önce gelir. Ürün sayısının binlerle değil, yüzlerle ifade
edilmesi beklenir. Okunabilirlik mikro-optimizasyona tercih edilir.

---

## 18. Gelecek fikirler

Olası ileri geliştirmeler: sekmeli görsel galerileri için ışık kutusu
(lightbox), çok dillilik, SEO iyileştirmeleri, statik site dışa aktarımı.
Bunlar güncel kapsam dışıdır; durum için `docs/ROADMAP.md`'ye bakınız.
