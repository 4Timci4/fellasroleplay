# Fellas Roleplay Whitelist Bot

Bu Discord botu, Fellas Roleplay web sitesi için whitelist rol atama ve kontrol işlemlerini otomatikleştirmek için kullanılır.

## Özellikler

- Discord sunucusunda üyenin belirli bir role sahip olup olmadığını kontrol etme (1267646750789861537)
- Başvuru onaylandığı zaman kullanıcıya ses teyit rolü verme (1292884946662457425)
- HTTP API üzerinden kullanıcılara özel mesaj gönderme

## Kurulum

1. Node.js ve npm'i yükleyin: [https://nodejs.org/](https://nodejs.org/)

2. Bağımlılıkları yükleyin:
   ```bash
   cd discord-bot
   npm install
   ```

3. `config.json` dosyasını düzenleyin:
   ```json
   {
       "token": "BOT_TOKEN",
       "clientId": "CLIENT_ID",
       "serverId": "1267610711509438576",
       "roleControl": "1267646750789861537",
       "whitelistRoleId": "1292884946662457425",
       "httpPort": 3000
   }
   ```

4. Discord Developer Portal'da bot ayarlarını yapın:
   - [Discord Developer Portal](https://discord.com/developers/applications)'a gidin
   - Uygulamanızı seçin
   - "Bot" sekmesine tıklayın
   - "Privileged Gateway Intents" bölümünde şu seçenekleri aktifleştirin:
     - SERVER MEMBERS INTENT

5. Botu Discord sunucunuza ekleyin:
   - "OAuth2" sekmesine geçin
   - "URL Generator" alt sekmesini seçin
   - "Scopes" bölümünde "bot" seçeneğini işaretleyin
   - "Bot Permissions" bölümünde en azından "Manage Roles" iznini seçin
   - Oluşturulan URL'yi kopyalayın ve tarayıcınızda açın
   - Botunuzu sunucunuza ekleyin ve izinleri onaylayın

## Çalıştırma

Windows'ta kolay başlatmak için:
```
start-bot.bat
```

Alternatif olarak:
```bash
npm start
```

veya

```bash
node index.js
```

## HTTP API Kullanımı

Bot, aşağıdaki HTTP API endpoint'lerini sunar:

### Rol Kontrolü

Bir kullanıcının 1267646750789861537 rolüne sahip olup olmadığını kontrol eder.

```
GET http://localhost:3000/check-role/DISCORD_USER_ID
```

Başarılı yanıt örneği:
```json
{
  "success": true,
  "hasRole": true,
  "username": "KullanıcıAdı",
  "roles": ["rol1_id", "rol2_id", "1267646750789861537"]
}
```

### Rol Atama

Onaylanmış başvurular için kullanıcıya ses teyit rolü (1292884946662457425) atar.

```
PUT http://localhost:3000/assign-role/DISCORD_USER_ID
```

Başarılı yanıt örneği:
```json
{
  "success": true,
  "hasRole": true,
  "message": "Rol başarıyla atandı",
  "username": "KullanıcıAdı"
}
```

### Özel Mesaj Gönderme

Belirli bir Discord kullanıcısına özel mesaj gönderir.

```
POST http://localhost:3000/send-dm/DISCORD_USER_ID
```

İstek gövdesi:
```json
{
  "message": "Gönderilecek mesaj içeriği"
}
```

Başarılı yanıt örneği:
```json
{
  "success": true,
  "message": "Mesaj başarıyla gönderildi"
}
```

## Önemli Notlar

1. Bot çalışırken, PHP uygulamanız HTTP API üzerinden rol kontrolü ve rol atama işlemlerini gerçekleştirebilir.

2. Botun rol atama işlemlerini gerçekleştirebilmesi için, Discord sunucunuzda "Manage Roles" iznine sahip olması gerekiyor.

3. Bot'un rol hiyerarşisinde, atayacağı "ses teyit" rolünden daha üstte olması gerekiyor.

4. Botun sürekli çalışması için bir sunucuda veya bilgisayarınızda sürekli çalıştırmanız gerekiyor.

5. Kullanıcı Discord sunucusunda bulunmuyorsa rol atanamaz, bu durumda API uygun hata mesajı döndürür.
