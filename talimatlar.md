# 3D Harikalar Diyarı — Talimatlar ve Devir Notu

> Bu dosya, projeyi devralan herkesin (yeni bir oturumdaki yapay zeka dahil)
> **önce okuması gereken** belgedir. Amacı: projeyi, geçmişini, mevcut
> durumunu, kurallarını ve sıradaki işleri baştan anlatmak zorunda kalmadan
> aktarmak. Bir çelişki olursa bu belge geçerlidir.

---

## 0. Hızlı özet

Bu proje, **Markdown'dan statik bir mağaza sitesi üreten** küçük bir PHP
aracıdır. Sahibi Türkçe konuşuyor; **yanıtlar Türkçe olmalı.** Site adı
**3D Harikalar Diyarı**. Her ürün bir hikâyedir; satış ikincildir. OpenCart
bir zamanlar vardı, **tamamen kaldırıldı — geri getirilmeyecek.**

---

## 1. Önemli geçmiş (mutlaka oku)

- Proje başta bir **OpenCart 4 eklentisiydi** (Markdown'dan OpenCart'a ürün
  içe aktarma). Adı hâlâ depoda `opencart-story-publisher` olabilir.
- Uzun bir yolculuğun sonunda **OpenCart tamamen kaldırıldı** ve proje
  **bağımsız bir statik site üreticisine** dönüştü.
- **Neden:** İhtiyaç "hikâyeleri yayınlamak + basit, güvenli satış"tı. Sipariş
  paneli, müşteri hesabı, stok gerekmiyordu. OpenCart'ın karmaşıklığının çoğu
  (ücretli dil paketleri, .htaccess/SEO, önbellek, veritabanı göçleri, sürüm
  uyumsuzlukları) bu ihtiyaçla ilgisizdi ve çok zaman kaybettirdi.
- Detaylı gerekçe: `docs/DECISIONS.md` → **Karar 8**.
- **KURAL: OpenCart'ı geri getirme.** Veritabanı, PHP sunucusu, admin paneli
  ekleme. İhtiyaç doğarsa bile önce sahibine sor.

---

## 2. Bu proje bugün ne yapıyor

- Her ürün `products/<klasör>/` altında yaşar: bir `product.md`, bir `images/`
  ve bir `downloads/` klasörü.
- `product.md`: üstte metadata (front matter), altta Markdown hikâye. Her üst
  düzey `#` başlığı ürün sayfasında ayrı bir **sekme** olur.
- `php build.php` çalıştırılınca `output/` altına **tam bir statik site**
  yazılır: katalog, ürün sayfaları, Hakkımızda. Görseller kopyalanır ve
  gerekiyorsa küçültülür.
- Site çerçevesizdir (Bootstrap yok); kendi `assets/style.css` ve `tabs.js`'i
  vardır. Veritabanı/sunucu gerekmez; `output/` her yere konabilir.

**Pipeline:** `Scanner → ProductParser → MarkdownRenderer (sekmeler+galeriler)
→ ImageOptimizer → SiteBuilder → output/`. Ayrıntı: `docs/ARCHITECTURE.md`.

---

## 3. Şu an ne var, ne çalışıyor

- 6 **örnek** ürün (placeholder görseller), 2 kategori: "3D Modeller",
  "Kalıplar". Bunlar gerçek ürünlerle değiştirilecek.
- Çalışan özellikler: sekmeli hikâyeler, arka arkaya görsellerin otomatik
  **galeri** olması, **görsel optimizasyonu** (varsayılan max 1200px),
  **sol menüde kategori filtresi** ("Tüm Ürünler" + kategoriler),
  **Hakkımızda** sayfası (kök dizindeki `about.md`'den; sil → kaybolur).
- Testler: `php tests/run.php` (pipeline'ı kapsar).
- **"Satın Al" butonu yer tutucudur** — ödeme henüz bağlı değil.

---

## 4. Sıradaki işler (öncelik sırasıyla)

1. **İçerik** — sahibi/kardeşi gerçek ürünleri, görselleri ve `about.md`'yi
   dolduracak. (Şu an burada.)
2. **Gerçek ödeme** — "Satın Al"ı bağlamak. Alıcılar önce **Türkiye'de**;
   bu yüzden **iyzico veya PayTR** + ödeme sonrası **güvenli dosya teslimi**
   (küçük bir sunucusuz fonksiyon). İleride yurt dışı için Stripe/Lemon
   Squeezy eklenebilir. İndirilebilir dosyalar statik sitede yayınlanmaz;
   ödeme sağlayıcısı teslim eder.
3. **Otomatik yayın** — `git push` → CI (`php build.php`) → statik host'a
   (GitHub Pages / Netlify / Cloudflare Pages) dağıtım.

Tam liste: `docs/ROADMAP.md`.

---

## 5. Çalışma kuralları (yapay zeka için)

- **Türkçe yanıt ver.**
- **Basit tut.** Gereksiz soyutlama, mimari, arayüz, DI, factory, erken
  optimizasyon yok. Okunur kod > akıllı kod.
- **Over-engineer etme.** Bugünün gereğini yap; spekülatif özellik ekleme.
- **Mimariyi açık talimat olmadan değiştirme.** Birden çok geçerli çözüm
  varsa, başlangıç seviyesi bir PHP geliştiricisinin anlayacağını seç.
- **Küçük commit'ler**, her adımda çalışan yazılım. Büyük yeniden yazımlardan
  kaçın.
- **Hatalar insan-okunur olmalı:** hangi ürün, ne, nasıl düzeltilir. Ham PHP
  hatası gösterme.
- **Mimari değişince önce dokümanı güncelle** (`docs/` + gerekiyorsa README).
  Güncelliğini yitirmiş doküman bir hatadır.
- **Dış bağımlılık** ancak ciddi bir problemi çözüyorsa (tek örnek: Markdown
  için `lib/Parsedown.php`).
- Kod yorumları İngilizce (geliştiriciye yönelik); **kullanıcıya dönük her şey
  Türkçe.**

---

## 6. Sahibinin bilinen tercihleri (bu yolculukta öğrenildi)

- **İçerik editörü programcı değil** ve **Windows'ta** çalışacak (sahibinin
  kardeşi). Onun için her şeyi kolay tut; README'de Windows kılavuzu var.
- Sahibi Linux'ta test ediyor, kardeşi Windows'ta yönetecek.
- Tasarım yönü: **soğuk nötrler + tek cesur aksan (erimiş filament turuncusu)**,
  başlıkta **FDM katman-çizgisi** dokusu. Klişe "AI tasarımı"ndan kaçın
  (asit-yeşili, mor gradyan, her şey ortalı, Inter/Space Grotesk).
- Metinlerde ton: sıcak, **esprili**, maker ruhu (bkz. `about.md`).
- Kategoriler çoğaldığında sol menü/filtre mantığı korunmalı (100 üründe bile
  kategori kaybolmasın).
- Ürün **kodu = klasör adı** (model yazmak zorunlu değil). Açıklayıcı klasör
  adları tercih ediliyor (`servo-yatagi`), kod-numara (`MDL-001`) değil.

---

## 7. Belgeler nerede

- `README.md` — kullanım + **Windows kılavuzu** (içerik editörü için)
- `docs/ARCHITECTURE.md` — kodun yapısı ve akışı
- `docs/SPECIFICATION.md` — ne yaptığının tanımı
- `docs/MAINTENANCE.md` — kurulum, barındırma, sorun giderme
- `docs/ROADMAP.md` — sıradaki işler
- `docs/DECISIONS.md` — mimari kararlar (özellikle **Karar 8: OpenCart'ı
  bırakma**)

---

## 8. Depo ve dal

- Depo GitHub'da **`3d-harikalar-diyari`** olarak yeniden adlandırıldı (eski:
  `opencart-story-publisher`). Yerelde klasör adı farklı olabilir; önemli değil.
- Çalışma dalı: `claude/version-0-1-implementation-9fm0fn`. **Bu ada takılma** —
  "version-0-1" ve "opencart" geçmiş kalıntısıdır; proje artık statik site.

---

## 9. Uzun vadeli hedef

Nihai iş akışı şu olmalı:

Obsidian'da hikâye yaz → `product.md` kaydet → görselleri koy → STL'leri koy →
`php build.php` (veya `git push`) → **statik site otomatik güncellenir.**

Başka hiçbir şey. Kararsız kaldığında, projeyi bu akışa yaklaştıran çözümü seç.

---

## 10. Kısaca felsefe

Markdown tek doğruluk kaynağıdır. **Yazar yazar, yazılım yayınlar.** Ürün bir
hikâyedir; müşteri parçanın neden var olduğunu anlamalı. Satış ikincildir.
Editörün dokunduğu tek şey `product.md` (ve görsel/dosya klasörleri) olmalı —
HTML, CSS, şablon değil.
