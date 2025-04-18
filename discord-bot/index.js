// Modülleri yükle
console.log('Modülleri yükleniyor...');
const { Client, GatewayIntentBits } = require('discord.js');
const express = require('express');
const bodyParser = require('body-parser');
console.log('Modüller yüklendi.');
const config = require('./config.json');

// Mesaj önbelleği - tekrarlanan mesajları önlemek için
const messageCache = new Map();
// Önbellek süresi (milisaniye cinsinden) - 30 saniye
const CACHE_DURATION = 30 * 1000;

// Config'den değerleri al
const { token, serverId, roleControl, whitelistRoleId, httpPort } = config;

// Bot istemcisini oluştur - sadece gerekli intentler
const client = new Client({
  intents: [
    GatewayIntentBits.Guilds,
    GatewayIntentBits.GuildMembers
  ]
});

// Bot hazır olduğunda
client.once('ready', async () => {
  console.log(`${client.user.tag} olarak giriş yapıldı!`);
  
  // Sunucuyu kontrol et
  const guild = client.guilds.cache.get(serverId);
  if (guild) {
    console.log(`Bot "${guild.name}" sunucusunda aktif.`);
  } else {
    console.error(`Hata: ${serverId} ID'li sunucu bulunamadı!`);
  }
  
  // Bot durumunu ayarla
  client.user.setPresence({
    activities: [{ name: 'Fellas Roleplay', type: 0 }],
    status: 'online'
  });
});

// Hata yakalama
client.on('error', error => {
  console.error('Discord client error:', error);
});

process.on('unhandledRejection', error => {
  console.error('Unhandled promise rejection:', error);
});

// HTTP sunucusu oluştur
const app = express();
app.use(bodyParser.json());

// CORS desteği ekle
app.use((req, res, next) => {
  res.header('Access-Control-Allow-Origin', '*');
  res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept');
  res.header('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
  
  // OPTIONS isteklerini hemen yanıtla
  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }
  
  next();
});

// Özel mesaj gönderme fonksiyonu
async function sendDirectMessage(userId, message) {
  try {
    // Mesaj önbelleğini kontrol et
    const cacheKey = `${userId}:${message}`;
    const cachedMessage = messageCache.get(cacheKey);
    
    // Eğer aynı mesaj son 30 saniye içinde gönderilmişse, tekrar gönderme
    if (cachedMessage && (Date.now() - cachedMessage) < CACHE_DURATION) {
      console.log(`Tekrarlanan mesaj engellendi: ${userId} kullanıcısına "${message.substring(0, 30)}..." mesajı zaten gönderilmiş.`);
      return true; // Başarılı olarak işaretle, çünkü mesaj zaten gönderilmiş
    }
    
    const user = await client.users.fetch(userId);
    if (user) {
      await user.send(message);
      
      // Mesajı önbelleğe ekle
      messageCache.set(cacheKey, Date.now());
      
      // Önbelleği temizle (30 saniye sonra)
      setTimeout(() => {
        messageCache.delete(cacheKey);
      }, CACHE_DURATION);
      
      return true;
    }
    return false;
  } catch (error) {
    console.error('Özel mesaj gönderilirken hata oluştu:', error);
    return false;
  }
}

// Özel mesaj gönderme için endpoint
app.post('/send-dm/:discordId', async (req, res) => {
  try {
    const discordId = req.params.discordId;
    const message = req.body.message;
    
    if (!message) {
      return res.status(400).json({
        success: false,
        error: 'Mesaj içeriği gereklidir'
      });
    }
    
    // Discord ID formatını doğrula
    if (!discordId || !/^\d{17,20}$/.test(discordId)) {
      return res.status(400).json({ 
        success: false, 
        error: 'Geçersiz Discord ID formatı. 17-20 rakam içermelidir.' 
      });
    }
    
    const result = await sendDirectMessage(discordId, message);
    
    return res.json({
      success: result,
      message: result ? 'Mesaj başarıyla gönderildi' : 'Mesaj gönderilemedi'
    });
  } catch (error) {
    console.error('Özel mesaj gönderme isteği sırasında hata:', error);
    return res.status(500).json({
      success: false,
      error: 'Sunucu hatası: ' + error.message
    });
  }
});

// Rol kontrolü için endpoint
app.get('/check-role/:discordId', async (req, res) => {
  try {
    const discordId = req.params.discordId;
    
    // Kullanıcı ID formatını doğrula
    if (!discordId || !/^\d{17,20}$/.test(discordId)) {
      return res.status(400).json({ 
        success: false, 
        hasRole: false, 
        error: 'Geçersiz Discord ID formatı. 17-20 rakam içermelidir.' 
      });
    }
    
    // Bot hazır değilse hata döndür
    if (!client.isReady()) {
      return res.status(503).json({ 
        success: false, 
        hasRole: false, 
        error: 'Discord botu henüz hazır değil' 
      });
    }
    
    // Guild'i kontrol et
    const guild = client.guilds.cache.get(serverId);
    if (!guild) {
      return res.status(404).json({ 
        success: false, 
        hasRole: false, 
        error: 'Discord sunucusu bulunamadı' 
      });
    }
    
    try {
      // İlk önce, genel Discord platformunda kullanıcının var olup olmadığını kontrol edelim
      const userCheck = await client.users.fetch(discordId);
      console.log(`Discord kullanıcısı bulundu: ${userCheck.username} (${discordId})`);
      
      try {
        // Ardından, kullanıcının sunucuda olup olmadığını kontrol edelim
        const member = await guild.members.fetch(discordId);
        
        // Rol kontrolü - 1267646750789861537 rolüne sahip mi?
        const hasRole = member.roles.cache.has(roleControl);
        
        // Sonucu döndür
        return res.json({ 
          success: true, 
          hasRole: hasRole,
          username: member.user.username,
          roles: [...member.roles.cache.keys()]
        });
      } catch (guildMemberError) {
        // Kullanıcı Discord'da var ancak sunucuda yok
        console.log(`Kullanıcı Discord'da var ancak sunucuda yok: ${userCheck.username} (${discordId})`);
        return res.status(404).json({ 
          success: false, 
          hasRole: false, 
          userExists: true,
          username: userCheck.username,
          error: 'Kullanıcı Discord platformunda var fakat sunucunuzda üye değil',
          errorCode: 'USER_NOT_IN_GUILD'
        });
      }
    } catch (userFetchError) {
      // Kullanıcı genel Discord platformunda bulunamadı
      console.log(`Discord kullanıcısı bulunamadı: ${discordId}`);
      return res.status(404).json({ 
        success: false, 
        hasRole: false, 
        userExists: false,
        error: 'Kullanıcı Discord platformunda bulunamadı. Geçersiz Discord ID.',
        errorCode: 'USER_NOT_EXISTS'
      });
    }
  } catch (error) {
    console.error('Rol kontrolü sırasında hata:', error);
    return res.status(500).json({ 
      success: false, 
      hasRole: false, 
      error: 'Sunucu hatası: ' + error.message 
    });
  }
});

// Whitelist rol atama için endpoint
app.put('/assign-role/:discordId', async (req, res) => {
  try {
    const discordId = req.params.discordId;
    
    // Kullanıcı ID formatını doğrula
    if (!discordId || !/^\d{17,20}$/.test(discordId)) {
      return res.status(400).json({ 
        success: false, 
        error: 'Geçersiz Discord ID formatı. 17-20 rakam içermelidir.' 
      });
    }
    
    // Bot hazır değilse hata döndür
    if (!client.isReady()) {
      return res.status(503).json({ 
        success: false, 
        error: 'Discord botu henüz hazır değil' 
      });
    }
    
    // Guild'i kontrol et
    const guild = client.guilds.cache.get(serverId);
    if (!guild) {
      return res.status(404).json({ 
        success: false, 
        error: 'Discord sunucusu bulunamadı' 
      });
    }
    
    try {
      // Önce kullanıcının Discord platformunda var olup olmadığını kontrol edelim
      const user = await client.users.fetch(discordId);
      console.log(`Discord kullanıcısı bulundu: ${user.username} (${discordId})`);
      
      try {
        // Sonra kullanıcının sunucuda olup olmadığını kontrol edelim
        const member = await guild.members.fetch(discordId);
        console.log(`Discord sunucu üyesi bulundu: ${member.user.username} (${discordId})`);
        
        // Kullanıcı zaten role sahip mi kontrol et
        const hasRole = member.roles.cache.has(whitelistRoleId);
        if (hasRole) {
          console.log(`Kullanıcı zaten bu role sahip: ${member.user.username} (${discordId}), roleId: ${whitelistRoleId}`);
          return res.json({
            success: true,
            hasRole: true,
            message: 'Kullanıcı zaten bu role sahip',
            username: member.user.username
          });
        }
        
        // Rol ata - 1292884946662457425 (ses teyit) rolü
        console.log(`Rol atanıyor... roleId: ${whitelistRoleId}, discordId: ${discordId}`);
        await member.roles.add(whitelistRoleId);
        console.log(`Rol başarıyla atandı: ${member.user.username} (${discordId}), roleId: ${whitelistRoleId}`);
        
        return res.json({
          success: true,
          hasRole: true,
          message: 'Rol başarıyla atandı',
          username: member.user.username
        });
      } catch (memberError) {
        console.error('Sunucu üyesi bulunamadı veya role atanırken hata oluştu:', memberError);
        return res.status(404).json({
          success: false,
          error: 'Kullanıcı Discord sunucusunda bulunamadı veya rol atanamadı',
          errorCode: 'USER_NOT_IN_GUILD',
          message: memberError.message
        });
      }
    } catch (userError) {
      console.error('Discord kullanıcısı bulunamadı:', userError);
      return res.status(404).json({
        success: false,
        error: 'Discord kullanıcısı bulunamadı',
        errorCode: 'USER_NOT_EXISTS',
        message: userError.message
      });
    }
  } catch (error) {
    console.error('Rol atama API hatası:', error);
    return res.status(500).json({
      success: false,
      error: 'Sunucu hatası: ' + error.message
    });
  }
});

// HTTP sunucusunu başlat (tüm IP adreslerinden gelen istekleri dinle)
const httpServer = app.listen(httpPort, '0.0.0.0', () => {
  console.log(`HTTP sunucusu port ${httpPort} üzerinde başlatıldı (tüm IP adresleri)`);
  console.log(`Rol kontrolü için: http://127.0.0.1:${httpPort}/check-role/[DISCORD_ID]`);
  console.log(`Rol atama için: http://127.0.0.1:${httpPort}/assign-role/[DISCORD_ID]`);
  console.log(`Mesaj gönderme için: http://127.0.0.1:${httpPort}/send-dm/[DISCORD_ID]`);
});

// Botu başlat
client.login(token).catch(error => {
  console.error('Bot giriş hatası:', error);
});

console.log('Bot başlatılıyor...');
