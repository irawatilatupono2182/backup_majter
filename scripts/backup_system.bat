@echo off
REM ✅ CRITICAL FIX #8: Automated Backup System
REM This script runs daily backup for database and files

SET BACKUP_DIR=C:\laragon\backup\adamjaya
SET DATE=%date:~-4,4%%date:~-10,2%%date:~-7,2%
SET TIME=%time:~0,2%%time:~3,2%%time:~6,2%
SET TIMESTAMP=%DATE%_%TIME: =0%

SET DB_NAME=adamjaya
SET DB_USER=root
SET DB_PASS=

REM Create backup directory if not exists
if not exist "%BACKUP_DIR%\database" mkdir "%BACKUP_DIR%\database"
if not exist "%BACKUP_DIR%\storage" mkdir "%BACKUP_DIR%\storage"
if not exist "%BACKUP_DIR%\logs" mkdir "%BACKUP_DIR%\logs"

echo ========================================
echo  ADAM JAYA - BACKUP SYSTEM
echo  Started: %DATE% %TIME%
echo ========================================

REM 1. Backup Database
echo [1/4] Backing up database...
mysqldump -u %DB_USER% --password=%DB_PASS% --single-transaction --quick --lock-tables=false %DB_NAME% > "%BACKUP_DIR%\database\%DB_NAME%_%TIMESTAMP%.sql"
if %ERRORLEVEL% EQU 0 (
    echo ✅ Database backup successful
) else (
    echo ❌ Database backup FAILED!
    echo %DATE% %TIME% - Database backup FAILED >> "%BACKUP_DIR%\logs\backup_errors.log"
)

REM 2. Backup Storage Files (invoices, attachments, etc)
echo [2/4] Backing up storage files...
xcopy /E /I /Y "C:\laragon\www\adamjaya\storage\app" "%BACKUP_DIR%\storage\app_%TIMESTAMP%"
if %ERRORLEVEL% EQU 0 (
    echo ✅ Storage backup successful
) else (
    echo ❌ Storage backup FAILED!
    echo %DATE% %TIME% - Storage backup FAILED >> "%BACKUP_DIR%\logs\backup_errors.log"
)

REM 3. Backup .env file (IMPORTANT!)
echo [3/4] Backing up .env configuration...
copy /Y "C:\laragon\www\adamjaya\.env" "%BACKUP_DIR%\env_%TIMESTAMP%.env"
if %ERRORLEVEL% EQU 0 (
    echo ✅ .env backup successful
) else (
    echo ❌ .env backup FAILED!
)

REM 4. Compress old backups (keep last 7 days)
echo [4/4] Cleaning old backups (keep last 7 days)...
forfiles /P "%BACKUP_DIR%\database" /M *.sql /D -7 /C "cmd /c del @path" 2>nul
forfiles /P "%BACKUP_DIR%\storage" /D -7 /C "cmd /c rmdir /S /Q @path" 2>nul

echo.
echo ========================================
echo ✅ BACKUP COMPLETED!
echo  Database: %BACKUP_DIR%\database\%DB_NAME%_%TIMESTAMP%.sql
echo  Storage: %BACKUP_DIR%\storage\app_%TIMESTAMP%
echo  Finished: %DATE% %TIME%
echo ========================================
echo.

REM Log success
echo %DATE% %TIME% - Backup completed successfully >> "%BACKUP_DIR%\logs\backup_success.log"

pause
