@echo off
REM === Automated MySQL Backup for daintyscapes ===

REM Set your MySQL credentials
set MYSQL_USER=root
set MYSQL_PASS=root
set MYSQL_DB=daintyscapes

REM Set backup directory
set BACKUP_DIR=C:\Users\JP\Desktop\KUSOGAKI\gaki_ProgramFiles\XAMPP\htdocs\backups

REM Create backup directory if it doesn't exist
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

REM Get date and time for filename (YYYY-MM-DD_HH-MM)
for /f "tokens=2-4 delims=/ " %%a in ('date /t') do (
    set mm=%%a
    set dd=%%b
    set yyyy=%%c
)
for /f "tokens=1-2 delims=: " %%a in ('time /t') do (
    set hh=%%a
    set min=%%b
)
set hh=%hh: =0%
set min=%min: =0%
set datetime=%yyyy%-%mm%-%dd%_%hh%-%min%

REM Set backup file name
set BACKUP_FILE=%BACKUP_DIR%\daintyscapes_%datetime%.sql

REM Perform the backup
if "%MYSQL_PASS%"=="" (
    "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe" -u%MYSQL_USER% %MYSQL_DB% > "%BACKUP_FILE%"
) else (
    "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe" -u%MYSQL_USER% -p%MYSQL_PASS% %MYSQL_DB% > "%BACKUP_FILE%"
)

REM Log the backup
echo [%date% %time%] Backup created: %BACKUP_FILE% >> "%BACKUP_DIR%\backup_log.txt"

echo Backup complete: %BACKUP_FILE%
pause