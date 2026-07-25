---
name: Servo Yatağı
model: SRV-001
price: 149
category: 3D Modeller
image: kapak.jpg
summary: Robot kolunuzdaki esnemeyi bitiren, desteksiz basılabilen sağlam bir servo yatağı; bir kez sıkıp yıllarca güvenle kullanmak için tasarlandı.
status: enabled
---

![](kapak.jpg)

# Sorun

Küçük robot kolumun her hareketinde bir tereddüt vardı. Motor yön
değiştirdiği anda kol birkaç derece geri esniyor, sonra yerine oturuyordu.
Gözle bakınca "çalışıyor" diyordunuz ama aynı noktayı iki kez üst üste
tutturmak imkânsızdı.

Sorunu uzun süre motorun kendisinde aradım. Daha güçlü bir servo aldım,
kabloları yeniledim, yazılımdaki PID değerleriyle günlerce oynadım. Hiçbiri
işe yaramadı. Sonunda kolu elimle tutup zorladığımda gerçeği anladım:
esneyen motor değil, motoru tutan **plastik braketti**.

![](stok-braket.jpg)

Stok braket ince PLA'dan basılmıştı ve vida deliklerinin çevresi neredeyse
kâğıt kadar zayıftı. Her yük değişiminde bu ince duvarlar hafifçe bükülüyor,
bu minik bükülme kolun ucunda milimetrelere dönüşüyordu. Bir oyuncak için
yeterliydi belki ama tekrarlanabilir bir makine için değil.

# Hayal

Kafamdaki parça çok netti: elime aldığımda **sağlam** hissettiren, bir kez
sıkıp bir daha hiç düşünmediğim bir yatak istiyordum. Bastırdığımda
esnemeyen, vidayı sıktığımda "tık" diye yerine oturan, on yıl sonra söküp
taktığımda hâlâ ilk günkü gibi tutan bir parça.

Sadece güçlü olması da yetmezdi. Destek malzemesi olmadan, tek seferde,
temiz basılmalıydı. Çünkü bu yatağı bir kere değil, farklı projelerde onlarca
kez basacağımı biliyordum. Her baskının ardından destekleri temizlemekle
uğraşmak istemiyordum.

Kısacası hayalim, "görünce özensiz bir hobi çıktısı değil, bir ürün parçası"
dedirten bir yataktı.

# İlk Prototip

İlk versiyonu bir akşamda çizdim ve gece boyunca bastım. Sabah elime
aldığımda hayal kırıklığıydı. Görüntüsü güzeldi ama ikinci kez vida
taktığımda deliklerden biri çatladı.

![](catlak.jpg)

Bu başarısızlık aslında çok öğreticiydi. Çatlağın tam olarak nereden
başladığına baktığımda, gerilimin duvarların en ince olduğu yerde
toplandığını gördüm. Yani sorun malzeme değil, **geometriydi**. Parçayı
daha kalın basmak yerine, yükün gittiği yolu değiştirmem gerekiyordu.

Bir de destek sorunu vardı: vidalı kulakların altı boşluktaydı ve yazıcı
oraya destek koymak zorunda kalıyordu. Destekleri temizlerken yüzey
pürüzlü kalıyor, bu da parçanın oturmasını bozuyordu.

# Geliştirme

Üç iterasyon sürdü. Her seferinde tek bir şeyi değiştirdim, çünkü aynı anda
beş şeyi değiştirirsen hangisinin işe yaradığını asla bilemezsin.

İlk olarak vida deliklerinin çevresindeki duvar kalınlığını 2 mm'den
3,2 mm'ye çıkardım ve deliklerin dibine küçük bir yaka ekledim. Böylece
vida sıkıldığında kuvvet tek bir noktaya değil, geniş bir yüzeye yayıldı.

![](gelistirme.jpg)

İkinci olarak, destek gerektiren tüm çıkıntıları 45 dereceden dik olmayacak
şekilde pahladım. Bu küçük değişiklik sayesinde parça artık hiç destek
istemeden basılıyordu. Baskı süresi kısaldı, yüzeyler pürüzsüz çıktı.

Son olarak tabana ince kaydırmaz bir çerçeve ekledim. Yatağı bir yüzeye
vidaladığınızda artık en ufak bir oynama bile hissetmiyorsunuz.

# Paylaşım

Bu yatağı kendim için tasarladım ama aynı sorunu yaşayan çok kişi olduğunu
biliyorum. O yüzden hem baskıya hazır **STL** dosyasını hem de kendi
servonuza göre uyarlayabilmeniz için **STEP** kaynak dosyasını ekledim.

![](final.jpg)

Standart 9 gramlık mikro servolar için birebir uyar. Farklı bir motor
kullanıyorsanız STEP dosyasını açıp delik aralığını birkaç saniyede
değiştirebilirsiniz. Önerilen baskı ayarları: %30 dolgu, 3 duvar, desteksiz.

# İlham

Yıllardır bir şey öğrendim: her makine, güvenebileceğiniz **tek bir
parçayla** başlar. Geri kalan her şeyi onun üzerine kurarsınız. O parça
esnemeye başladığı an, üstündeki bütün emek anlamını yitirir.

Servo Yatağı benim için o parça. Küçük, gösterişsiz, kimsenin fark etmediği
bir bileşen. Ama doğru yaptığınızda, üstüne kurduğunuz her şey bir anda
yerli yerine oturuyor. İyi tasarımın sessiz olması da bundan.
