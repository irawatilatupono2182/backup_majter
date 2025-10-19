#!/bin/bash
# Daily Database Backup Script for Si-Majter (Linux/Unix version)
# Run this script daily via cron job

BACKUP_DIR="/var/backups/si-majter/database"
DB_NAME="si_majter"
DB_USER="root"
DB_PASS=""
MYSQL_BIN="/usr/bin"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Generate timestamp for filename
TIMESTAMP=$(date +"%Y-%m-%d_%H-%M-%S")

# Create backup filename
BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_backup_${TIMESTAMP}.sql"

# Perform backup
echo "Creating database backup..."
if [ -z "$DB_PASS" ]; then
    "$MYSQL_BIN/mysqldump" -u "$DB_USER" --routines --triggers --single-transaction "$DB_NAME" > "$BACKUP_FILE"
else
    "$MYSQL_BIN/mysqldump" -u "$DB_USER" -p"$DB_PASS" --routines --triggers --single-transaction "$DB_NAME" > "$BACKUP_FILE"
fi

if [ $? -eq 0 ]; then
    echo "Backup created successfully: $BACKUP_FILE"
    
    # Compress backup file
    gzip "$BACKUP_FILE"
    if [ $? -eq 0 ]; then
        echo "Backup compressed to: ${BACKUP_FILE}.gz"
    fi
    
    # Clean old backups (keep only last 30 days)
    find "$BACKUP_DIR" -name "*.gz" -type f -mtime +30 -delete
    echo "Old backups cleaned (kept last 30 days)"
    
else
    echo "Backup failed with error code: $?"
fi

echo "Backup process completed at $(date)"