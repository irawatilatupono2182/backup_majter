<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DatabaseBackup extends Command
{
    /**
     * ✅ CRITICAL FIX #8: Automated Database Backup via Artisan
     *
     * @var string
     */
    protected $signature = 'backup:database 
                            {--keep=7 : Number of days to keep backups}';

    protected $description = 'Backup database dengan compression dan retention policy';

    public function handle()
    {
        $this->info('🔄 Starting database backup...');
        
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $backupPath = storage_path('backups/database');
        
        // Create backup directory if not exists
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }
        
        $filename = "adamjaya_backup_{$timestamp}.sql";
        $filepath = "{$backupPath}/{$filename}";
        
        // Get database credentials from config
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);
        
        // Build mysqldump command
        $command = sprintf(
            'mysqldump -h %s -P %s -u %s -p%s --single-transaction --quick --lock-tables=false %s > %s',
            escapeshellarg($host),
            $port,
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($filepath)
        );
        
        // Execute backup
        $this->line('📦 Creating backup file: ' . $filename);
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($filepath)) {
            $size = filesize($filepath);
            $sizeInMB = round($size / 1024 / 1024, 2);
            
            $this->info("✅ Backup successful! Size: {$sizeInMB} MB");
            $this->line("📁 Location: {$filepath}");
            
            // Compress backup
            $this->line('🗜️  Compressing backup...');
            exec("gzip {$filepath}", $output, $gzipCode);
            
            if ($gzipCode === 0 && file_exists("{$filepath}.gz")) {
                $compressedSize = filesize("{$filepath}.gz");
                $compressedSizeInMB = round($compressedSize / 1024 / 1024, 2);
                $this->info("✅ Compressed to {$compressedSizeInMB} MB");
                
                // Log to database
                DB::table('backup_logs')->insert([
                    'type' => 'database',
                    'filename' => "{$filename}.gz",
                    'file_size' => $compressedSize,
                    'status' => 'success',
                    'created_at' => now(),
                ]);
            }
            
            // Clean old backups
            $keepDays = $this->option('keep');
            $this->cleanOldBackups($backupPath, $keepDays);
            
            Log::channel('backup')->info('Database backup completed', [
                'filename' => $filename,
                'size' => $sizeInMB,
            ]);
            
            return 0;
        } else {
            $this->error('❌ Backup failed!');
            
            DB::table('backup_logs')->insert([
                'type' => 'database',
                'filename' => $filename,
                'status' => 'failed',
                'error_message' => 'Backup command failed',
                'created_at' => now(),
            ]);
            
            Log::channel('backup')->error('Database backup failed', [
                'command' => $command,
                'return_code' => $returnCode,
            ]);
            
            return 1;
        }
    }
    
    private function cleanOldBackups(string $path, int $keepDays): void
    {
        $this->line("🧹 Cleaning backups older than {$keepDays} days...");
        
        $files = glob("{$path}/*.sql.gz");
        $deleted = 0;
        
        foreach ($files as $file) {
            $fileTime = filectime($file);
            $daysOld = (time() - $fileTime) / 86400;
            
            if ($daysOld > $keepDays) {
                unlink($file);
                $deleted++;
            }
        }
        
        if ($deleted > 0) {
            $this->info("✅ Deleted {$deleted} old backup(s)");
        } else {
            $this->line('No old backups to delete');
        }
    }
}
