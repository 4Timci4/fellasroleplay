# VSCode'u GitHub Hesabınıza Bağlama Rehberi

Bu rehber, Visual Studio Code (VSCode) editörünüzü GitHub hesabınıza bağlama ve projenizi GitHub'a yükleme adımlarını açıklar.

## Gereksinimler

1. [Git](https://git-scm.com/downloads) bilgisayarınızda kurulu olmalı
2. [GitHub](https://github.com/) hesabınız olmalı
3. [Visual Studio Code](https://code.visualstudio.com/) kurulu olmalı

## Git Kullanıcı Bilgilerini Değiştirme

Eğer Git'i farklı bir GitHub hesabı ile kullanmak istiyorsanız, kullanıcı bilgilerinizi değiştirebilirsiniz:

### Global Olarak Değiştirme (Tüm Projeler İçin)

VSCode'da terminal açın (Ctrl+` veya Terminal > New Terminal) ve aşağıdaki komutları çalıştırın:

```bash
# Mevcut ayarları kontrol et
git config --global user.name
git config --global user.email

# Yeni bilgileri ayarla
git config --global user.name "Yeni GitHub Kullanıcı Adınız"
git config --global user.email "yeni.email@adresiniz.com"
```

### Sadece Bu Proje İçin Değiştirme

Eğer sadece bu proje için farklı bir GitHub hesabı kullanmak istiyorsanız:

```bash
# Proje klasöründe olduğunuzdan emin olun
cd c:/Users/Administrator/Desktop/fellasroleplay

# Sadece bu proje için kullanıcı bilgilerini ayarla (--global olmadan)
git config user.name "Proje İçin GitHub Kullanıcı Adınız"
git config user.email "proje.icin@email.adresiniz.com"
```

### Kimlik Bilgilerini Sıfırlama

Windows'ta kayıtlı GitHub kimlik bilgilerini sıfırlamak için:

1. Windows Kimlik Bilgileri Yöneticisi'ni açın:
   - Başlat > Kimlik Bilgileri Yöneticisi'ni arayın ve açın
   
2. "Windows Kimlik Bilgileri" altında GitHub ile ilgili girişleri bulun:
   - `git:https://github.com` gibi girişleri bulun
   
3. Bu girişleri seçin ve "Kaldır" düğmesine tıklayın

Alternatif olarak, komut satırından:

```bash
# Windows'ta GitHub kimlik bilgilerini sıfırla
cmdkey /delete:LegacyGeneric:target=git:https://github.com

# veya Git kimlik yardımcısını kullanarak
git credential-manager delete https://github.com
```

### 2. GitHub'da Yeni Bir Depo (Repository) Oluşturma

1. GitHub hesabınıza giriş yapın
2. Sağ üst köşedeki "+" simgesine tıklayın ve "New repository" seçin
3. Depo adını girin (örneğin "fellasroleplay")
4. İsteğe bağlı olarak bir açıklama ekleyin
5. Deponun "Public" (herkese açık) veya "Private" (özel) olmasını seçin
6. "Initialize this repository with a README" seçeneğini İŞARETLEMEYİN (zaten bir README dosyanız var)
7. "Create repository" düğmesine tıklayın

### 3. VSCode'da GitHub Uzantısını Kurma

1. VSCode'da uzantılar sekmesine tıklayın (sol kenar çubuğunda veya Ctrl+Shift+X)
2. Arama kutusuna "GitHub" yazın
3. "GitHub Pull Requests and Issues" uzantısını bulun ve "Install" düğmesine tıklayın
4. Uzantı kurulduktan sonra, sol kenar çubuğunda GitHub simgesine tıklayın
5. "Sign in to GitHub" seçeneğine tıklayarak GitHub hesabınıza giriş yapın

### 4. Projenizi GitHub'a Yükleme

#### A. Yeni Bir Git Deposu Başlatma

1. VSCode'da terminal açın (Ctrl+` veya Terminal > New Terminal)
2. Proje klasörünüzde olduğunuzdan emin olun (şu anda: c:/Users/Administrator/Desktop/fellasroleplay)
3. Aşağıdaki komutları sırasıyla çalıştırın:

```bash
# Git deposu başlat
git init

# Tüm dosyaları ekle
git add .

# İlk commit'i oluştur
git commit -m "İlk commit: Proje dosyaları"
```

#### B. GitHub Deponuzu Uzak Depo Olarak Ekleme

GitHub'da oluşturduğunuz deponun URL'sini kullanarak:

```bash
# GitHub deponuzu uzak depo olarak ekleyin
git remote add origin https://github.com/KULLANICI_ADINIZ/DEPO_ADINIZ.git

# Yerel deponuzu GitHub'a gönderin
git push -u origin master
# veya
git push -u origin main
```

Not: GitHub'ın varsayılan dal adı "main" olabilir, bu durumda "master" yerine "main" kullanın.

### 5. .gitignore Dosyası Oluşturma (Önerilen)

Hassas bilgileri ve gereksiz dosyaları GitHub'a yüklemekten kaçınmak için bir `.gitignore` dosyası oluşturabilirsiniz:

```bash
# .gitignore dosyası oluştur
echo "# Hassas bilgiler ve geçici dosyalar" > .gitignore
echo "logs/" >> .gitignore
echo "*.log" >> .gitignore
echo ".env" >> .gitignore
echo "node_modules/" >> .gitignore
```

Bu dosyayı oluşturduktan sonra, değişiklikleri commit edin ve GitHub'a gönderin:

```bash
git add .gitignore
git commit -m ".gitignore dosyası eklendi"
git push
```

### 6. VSCode'da Git İşlemleri

VSCode'un sol kenar çubuğundaki "Source Control" (Kaynak Kontrolü) sekmesi (veya Ctrl+Shift+G), Git işlemlerini kolayca yönetmenizi sağlar:

- Değişiklikleri görebilir
- Dosyaları commit edebilir
- Dalları değiştirebilir
- Push ve pull işlemlerini yapabilirsiniz

## Sorun Giderme

### GitHub Kimlik Doğrulama Sorunları

Eğer push yaparken kimlik doğrulama hatası alırsanız:

1. GitHub'da kişisel erişim token'ı oluşturun:
   - GitHub > Settings > Developer settings > Personal access tokens > Generate new token
   - Token'a uygun izinleri verin (repo, workflow, vb.)
   - Token'ı kopyalayın

2. Push yaparken kullanıcı adı olarak GitHub kullanıcı adınızı, şifre olarak oluşturduğunuz token'ı kullanın

### Git LFS (Büyük Dosyalar İçin)

Eğer projenizde büyük dosyalar varsa (örneğin, resimler, videolar), Git LFS (Large File Storage) kullanmayı düşünebilirsiniz:

```bash
# Git LFS kurulumu
git lfs install

# Büyük dosya türlerini belirtin
git lfs track "*.png" "*.jpg" "*.mp4"

# .gitattributes dosyasını commit edin
git add .gitattributes
git commit -m "Git LFS yapılandırması eklendi"
```

## Sonraki Adımlar

- GitHub'da Issues (Sorunlar) ve Projects (Projeler) özelliklerini kullanarak proje yönetimini iyileştirebilirsiniz
- GitHub Actions ile otomatik dağıtım (deployment) yapılandırabilirsiniz
- GitHub Pages ile web sitenizi doğrudan GitHub üzerinden yayınlayabilirsiniz
