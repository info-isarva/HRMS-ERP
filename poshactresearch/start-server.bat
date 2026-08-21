@echo off
echo ISARVA POSH - Starting local server...
echo Open in browser: http://localhost:8080
echo Share on LAN: http://YOUR-PC-IP:8080
cd /d "%~dp0"
python -m http.server 8080 2>nul || py -m http.server 8080
pause
