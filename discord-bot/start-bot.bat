@echo on
echo Fellas Roleplay Whitelist Bot baslatiliyor...
cd %~dp0
echo Dizin: %cd%
node index.js > bot_log.txt 2>&1
echo Hata kodu: %errorlevel%
echo Log dosyasi: bot_log.txt
type bot_log.txt
pause
