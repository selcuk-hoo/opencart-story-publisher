---
name: Çikolata Kalıbı
model: KLP-003
price: 79
category: Kalıplar
image: kapak.jpg
summary: Gıdaya uygun, esnek bir çikolata kalıbı; hafif konik gözleri sayesinde bonbonlar tek dokunuşta çıkar ve sonuç geometrik, pürüzsüz olur.
status: enabled
---

![](kapak.jpg)

# Sorun

Evde çikolata yapmayı seviyorum ama mağazadaki kalıplar beni hep aynı yere
sıkıştırıyordu: klasik kare bloklar, kalpler, birkaç hayvan figürü. Kendi
geometrik bonbonlarımı, hatta üzerinde küçük bir desen olan çikolataları
yapmak istediğimde çaresiz kalıyordum.

Kafamda net bir görüntü vardı: keskin köşeli, modern, tek lokmalık
geometrik bonbonlar. Bunu üretecek kalıp hiçbir mutfak dükkânında yoktu.
Elimdeki 3D yazıcı akla ilk gelen çözümdü ama çikolata, gıda ve 3D baskı bir
araya gelince işin içine iki ciddi mesele giriyordu.

# Hayal

Hayalim, gıdayla güvenle temas eden, çikolatanın kolayca çıktığı ve yüzeyi
pürüzsüz bırakan bir kalıptı. Bir kalıba çikolatayı dök, buzdolabına koy,
sonra ters çevirip hafifçe bük — bonbonlar kendiliğinden düşsün istiyordum.

Sadece şekil değil, doku da önemliydi. Çikolatanın o parlak, iştah açan
yüzeyi doğrudan kalıbın yüzeyinden gelir. Kalıp mat ve çizgiliyse çikolata
da öyle çıkar. Yani hem formu hem yüzeyi hem de gıda güvenliğini aynı anda
çözmem gerekiyordu.

# İlk Prototip

İlk kalıbı sert PLA ile bastım. İki sorun birden çıktı. Birincisi çikolata
kalıptan kolay çıkmıyordu; kalıbı zorlayınca bonbonların köşeleri kırılıyordu.

![](cikmayan.jpg)

İkincisi, çikolatanın yüzeyinde baskının katman izleri belirgin şekilde
görünüyordu. Bir de standart PLA'nın gıdayla doğrudan uzun teması konusunda
içim rahat değildi. Kısacası elimde şekli doğru ama kullanışsız ve tereddüt
uyandıran bir kalıp vardı.

# Geliştirme

Önce çıkarma sorununu çözdüm. Her göze hafif bir **koniklik** verdim; yani
gözler ağza doğru azıcık genişliyor. Bu küçük açı sayesinde çikolata,
kalıbı ters çevirdiğinde takılmadan düşüyor.

![](konik.jpg)

Sonra malzemeyi değiştirdim. Kalıbı esnek, gıdaya uygun **TPU** ile
bastım. Esneklik her şeyi değiştirdi: kalıbı hafifçe bükünce bonbonlar tek
tek fırlıyor. Gıdaya uygun filament kullanmak da güvenlik tarafını çözdü.
Yüzey için katman yüksekliğini düşürüp kalıbın iç yüzeyini gıdaya uygun bir
parlatıcıyla düzelttim; çikolata artık parlak çıkıyor.

![](tpu.jpg)

Son olarak göz tabanına isteğe bağlı, çıkıntılı ince bir desen ekledim;
böyle her bonbonun üzerinde küçük bir doku kalıyor.

# Paylaşım

Ekte baskıya hazır STL dosyası ve kendi bonbon şeklinizi ya da deseninizi
tasarlamak isterseniz STEP kaynağı var. Kalıp, standart bir buzluk gözü
boyutlarına uygun tasarlandı.

![](final.jpg)

Önemli: Kalıbı mutlaka **gıdaya uygun filament** ile basın ve gıdaya temas
eden yüzeylerin pürüzsüz olmasına dikkat edin. Önerilen malzeme esnek TPU;
çıkarmayı inanılmaz kolaylaştırıyor. Kullanımdan önce ılık suyla iyice
yıkayın.

# İlham

Bu proje bana mühendislikle mutfağın aslında ne kadar yakın olduğunu
hatırlattı. Bir bonbonun köşesindeki o küçük koniklik, tıpkı bir makine
parçasındaki pah gibi düşünülmüş bir detay. Görünmez ama her şeyi
değiştiriyor.

En sevdiğim an, ürettiğim bir şeyi birinin gülümseyerek yemesi. Tasarımın
çoğu zaman soyut kaldığı bir dünyada, çikolata onu en somut, en tatlı haline
getiriyor.
