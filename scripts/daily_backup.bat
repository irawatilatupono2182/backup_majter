@echo off
REM Daily Database Backup Script for Si-Majter
REM Run this script daily via Windows Task Scheduler

set BACKUP_DIR=C:\laragon\www\adamjaya\backups\database
set DB_NAME=si_majter
set DB_USER=root
set DB_PASS=
set MYSQL_BIN=C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin

REM Create backup directory if it doesn't exist
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

REM Generate timestamp for filename
for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value') do set "dt=%%a"
set "YY=%dt:~2,2%" & set "YYYY=%dt:~0,4%" & set "MM=%dt:~4,2%" & set "DD=%dt:~6,2%"
set "HH=%dt:~8,2%" & set "Min=%dt:~10,2%" & set "Sec=%dt:~12,2%"
set "timestamp=%YYYY%-%MM%-%DD%_%HH%-%Min%-%Sec%"

REM Create backup filename
set BACKUP_FILE=%BACKUP_DIR%\%DB_NAME%_backup_%timestamp%.sql

REM Perform backup
echo Creating database backup...
"%MYSQL_BIN%\mysqldump.exe" -u %DB_USER% --password=%DB_PASS% --routines --triggers --single-transaction %DB_NAME% > "%BACKUP_FILE%"

if %ERRORLEVEL% EQU 0 (
    echo Backup created successfully: %BACKUP_FILE%
    
    REM Compress backup file
    powershell "Compress-Archive -Path '%BACKUP_FILE%' -DestinationPath '%BACKUP_FILE%.zip'"
    if %ERRORLEVEL% EQU 0 (
        del "%BACKUP_FILE%"
        echo Backup compressed to: %BACKUP_FILE%.zip
    )
    
    REM Clean old backups (keep only last 30 days)
    forfiles /p "%BACKUP_DIR%" /s /m *.zip /d -30 /c "cmd /c del @path" 2>nul
    echo Old backups cleaned (kept last 30 days)
    
) else (
    echo Backup failed with error code: %ERRORLEVEL%
)

echo Backup process completed at %date% %time%
pause